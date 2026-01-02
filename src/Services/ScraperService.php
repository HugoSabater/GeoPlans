<?php

declare(strict_types=1);

namespace GeoPlans\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Psr\Log\LoggerInterface;

class ScraperService
{
    private Client $client;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new Client([
            'timeout' => 15.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            ],
            'verify' => false // Local dev fix
        ]);
        $this->logger = $logger;
    }

    /**
     * Scrapea múltiples fuentes.
     * @param array|string $sources
     */
    public function scrape($sources, int $maxPages = 3): array
    {
        if (is_string($sources)) {
            $sources = [$sources];
        }

        $allPlans = [];

        foreach ($sources as $sourceUrl) {
            $this->logger->info("--- Iniciando fuente: {$sourceUrl} ---");
            $sourcePlans = $this->scrapeSource($sourceUrl, $maxPages);
            $allPlans = array_merge($allPlans, $sourcePlans);
        }

        return $allPlans;
    }

    private function scrapeSource(string $baseUrl, int $maxPages): array
    {
        $plans = [];
        $isTeatroMadrid = strpos($baseUrl, 'teatromadrid.com') !== false;
        $isEsMadrid = strpos($baseUrl, 'esmadrid.com') !== false;

        for ($i = 1; $i <= $maxPages; $i++) {
            // Construir URL Paginada
            $url = $baseUrl;
            if ($i > 1) {
                if ($isTeatroMadrid) {
                    $url = rtrim($baseUrl, '/') . "/pagina/$i";
                } elseif ($isEsMadrid) {
                    $url = "{$baseUrl}?page={$i}"; // EsMadrid suele usar ?page=
                } else {
                    $separator = (strpos($baseUrl, '?') === false) ? '?' : '&';
                    $url = "{$baseUrl}{$separator}page={$i}";
                }
            }

            $this->logger->info("Scraping [P{$i}]: {$url}");

            try {
                $response = $this->client->get($url);
                if ($response->getStatusCode() !== 200) {
                    $this->logger->error("Skip: Status {$response->getStatusCode()}");
                    continue;
                }

                $html = (string) $response->getBody();
                $crawler = new Crawler($html);

                // Selector Maestro dependiente de la web
                if ($isTeatroMadrid) {
                    // TeatroMadrid: Selectores robustos (V1 + V2)
                    // TeatroMadrid: Selectores robustos (V1 + V2)
                    $nodes = $crawler->filter('.card li, .list-articulos li, article.node-event');
                } elseif ($isEsMadrid) {
                    // EsMadrid: Cards de eventos
                    $nodes = $crawler->filter('.card, .event-list-item, article.evento, .teaser');
                } else {
                    // Genérico
                    $nodes = $crawler->filter('article, .card, .item');
                }

                if ($nodes->count() === 0) {
                    $this->logger->warning("No se encontraron nodos en {$url} usando selectores estándar.");
                    continue;
                }

                $nodes->each(function (Crawler $node) use (&$plans, $url, $isTeatroMadrid, $isEsMadrid) {
                    // 1. TÍTULO Y FILTRADO
                    $titleNode = $node->filter('h2, h3, .title, .card-title');
                    if ($titleNode->count()) {
                        $title = trim($titleNode->text());
                    } else {
                        // Fallback: Link text or Node text
                        $linkNode = $node->filter('a')->first();
                        $title = $linkNode->count() ? trim($linkNode->text()) : trim($node->text());
                    }

                    // Filtros de Calidad (Anti-Basura)
                    if (empty($title))
                        return;
                    if (strlen($title) < 5)
                        return; // Muy corto
                    if (in_array(mb_strtolower($title), ['teatro', 'humor', 'música', 'danza', 'musicales', 'agenda', 'promociones', 'ver más', 'todas'])) {
                        // $this->logger->debug("Skip: Título genérico '{$title}'");
                        return;
                    }

                    // Blacklist de frases completas (Falso Positivo Crítico)
                    if (str_contains($title, 'Promociones Todas') || str_contains($title, 'Ver más')) {
                        return;
                    }

                    // 2. ENLACE
                    $linkNode = $node->filter('a')->first();
                    $link = $linkNode->count() ? $linkNode->attr('href') : $url;
                    if ($link && strpos($link, 'http') === false) {
                        $host = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
                        $link = $host . '/' . ltrim($link, '/');
                    }

                    // 3. CATEGORÍA
                    $categoryId = $this->detectCategory($title);

                    // 4. IMAGEN (Con lógica Lazy Load)
                    $imageUrl = $this->extractImage($node, $url);
                    if (!$imageUrl) {
                        $imageUrl = $this->getFallbackImage($categoryId);
                    }

                    // 5. FECHA (Parsing avanzado)
                    $dateRaw = '';
                    if ($isTeatroMadrid) {
                        // TeatroMadrid fecha suele estar en .field-name-field-fecha-rango o similar
                        $dateNode = $node->filter('.field--type-datetime, .date-display-range, .info-date');
                        $dateRaw = $dateNode->count() ? $dateNode->text() : '';
                    } else {
                        $dateNode = $node->filter('.date, .fecha, time');
                        $dateRaw = $dateNode->count() ? $dateNode->text() : '';
                    }

                    $realDate = $this->parseDate($dateRaw);

                    // 6. UBICACIÓN
                    $location = 'Madrid';
                    $locNode = $node->filter('.location, .txt-geo, .address');
                    if ($locNode->count()) {
                        $location = trim($locNode->text());
                    }

                    $plans[] = [
                        'title' => $title,
                        'description' => $title . ' - Entradas y más info en la web oficial.',
                        'location' => substr($location, 0, 100),
                        'price' => 0.0,
                        'date' => $realDate,
                        'url_source' => $link,
                        'category_id' => $categoryId,
                        'image_url' => $imageUrl
                    ];

                    // Validación Final de Calidad
                    if (empty($realDate) || $realDate === date('Y-m-d H:i:s')) {
                        // Si es la fecha de hoy por fallback, intentamos ser estrictos si se pide.
                        // Pero por ahora lo dejamos pasar como "Evento próximo"
                    }
                    if (strlen($title) < 5)
                        return; // Doble check
                });

                if ($i < $maxPages)
                    sleep(1); // Cortesía

            } catch (\Throwable $e) {
                $this->logger->error("Error en {$url}: " . $e->getMessage());
            }
        }

        return $plans;
    }

    private function detectCategory(string $title): int
    {
        $t = mb_strtolower($title);
        if (preg_match('/(concierto|música|jazz|rock|pop|banda)/i', $t))
            return 4; // Música
        if (preg_match('/(teatro|drama|comedia|monólogo|escena|musical)/i', $t))
            return 5; // Teatro
        if (preg_match('/(cine|película|proyección|corto)/i', $t))
            return 6; // Cine
        if (preg_match('/(gastron|tapa|vino|cena|brunch)/i', $t))
            return 2; // Gastronomía
        if (preg_match('/(expo|museo|arte|foto|pintura)/i', $t))
            return 1; // Cultura (Arte)
        return 7; // Aire Libre / Otros
    }

    private function parseDate(string $raw): string
    {
        if (empty($raw))
            return date('Y-m-d H:i:s');

        // Intentar buscar patrones dd/mm/yyyy o dd de Mes
        // Mapeo básico de meses
        $months = ['enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12];

        // Regex para "20 enero" o "20 de enero"
        if (preg_match('/(\d{1,2})\s+(?:de\s+)?([a-z]+)/iu', $raw, $matches)) {
            $day = $matches[1];
            $monthName = mb_strtolower($matches[2]);
            if (isset($months[$monthName])) {
                $year = date('Y'); // Asumimos año actual
                // Si el mes ya pasó, quizás es el año que viene (simple logic)
                if ($months[$monthName] < (int) date('n')) {
                    // $year++; // Opcional, mejor no arriesgar sin contexto
                }
                return date("Y-{$months[$monthName]}-$day 20:00:00");
            }
        }

        return date('Y-m-d H:i:s'); // Fallback a Hoy
    }

    private function extractImage(Crawler $node, string $baseUrl): ?string
    {
        $imgNode = $node->filter('img')->first();
        if (!$imgNode->count())
            return null;

        // Lazy Loading priority
        $src = $imgNode->attr('data-src') ?: $imgNode->attr('data-original') ?: $imgNode->attr('src');

        if (!$src || strpos($src, 'base64') !== false)
            return null;

        if (strpos($src, 'http') === false) {
            $src = parse_url($baseUrl, PHP_URL_SCHEME) . '://' . parse_url($baseUrl, PHP_URL_HOST) . '/' . ltrim($src, '/');
        }
        return $src;
    }

    private function getFallbackImage(int $catId): string
    {
        // Imágenes estables de Unsplash
        switch ($catId) {
            case 1:
                return 'https://images.unsplash.com/photo-1514525253440-b393452e8d26?w=800&q=80'; // Música
            case 2:
                return 'https://images.unsplash.com/photo-1503095392237-fa26b21ba375?w=800&q=80'; // Teatro
            case 3:
                return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800&q=80'; // Cine
            case 4:
                return 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80'; // Gastro
            default:
                return 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&q=80'; // Aire Libre
        }
    }
}