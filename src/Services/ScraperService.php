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
            'timeout' => 10.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8'
            ],
            'verify' => false // Desactivar verificación SSL para evitar errores en local (cURL 60)
        ]);
        $this->logger = $logger;
    }

    /**
     * Scrapea una URL recorriendo múltiples páginas.
     */
    public function scrape(string $baseUrl, int $maxPages = 5): array
    {
        $this->logger->info("Iniciando scraping MULTI-PÁGINA (Máx: {$maxPages}) en: {$baseUrl}");
        $allPlans = [];

        for ($i = 1; $i <= $maxPages; $i++) {
            // Construcción de URL paginada
            $url = $baseUrl;
            if ($i > 1) {
                // Lógica específica para TeatroMadrid o genérica
                if (strpos($baseUrl, 'teatromadrid') !== false) {
                    $url = rtrim($baseUrl, '/') . "/pagina/$i";
                } else {
                    // Paginación genérica estándar
                    $separator = (strpos($baseUrl, '?') === false) ? '?' : '&';
                    $url = "{$baseUrl}{$separator}page={$i}";
                }
            }

            $this->logger->info("Procesando Página {$i}: {$url}");

            try {
                $response = $this->client->get($url);
                $statusCode = $response->getStatusCode();

                if ($statusCode !== 200) {
                    $this->logger->error("Error: Status {$statusCode} en página {$i}");
                    continue; // Intentar siguiente página
                }

                $html = (string) $response->getBody();
                $crawler = new Crawler($html);

                // Selectores (Mismos que antes)
                $nodes = $crawler->filter('li.col-md-12, .list-articulos li, .l-list li, article.node-event, .views-row');

                if ($nodes->count() === 0) {
                    // Fallback
                    $nodes = $crawler->filter('.card, article, .item, .news-item');
                }

                $this->logger->info("Nodos encontrados en P{$i}: " . $nodes->count());

                $pageCount = 0;
                $nodes->each(function (Crawler $node) use (&$allPlans, &$pageCount, $url) {
                    // Título extendido
                    $titleNode = $node->filter('h2, h3, h4, .title, .card-title, .field-title a');
                    $title = $titleNode->count() ? trim($titleNode->text()) : '';

                    if (!$title) {
                        // Intento de fallback: tomar el texto del primer enlace
                        $linkTry = $node->filter('a')->first();
                        if ($linkTry->count()) {
                            $title = trim($linkTry->text());
                        }
                    }

                    if (!$title) {
                        $this->logger->info("Skip: Sin título en nodo.");
                        if ($pageCount === 0) {
                            $this->logger->info("HTML Nodo Debug: " . substr($node->html(), 0, 200));
                        }
                        return;
                    }
                    if (strlen($title) < 3) {
                        $this->logger->info("Skip: Título corto: $title");
                        return;
                    }
                    if (stripos($title, 'promo') !== false) {
                        return;
                    }

                    $linkNode = $node->filter('a');
                    $link = $linkNode->count() ? $linkNode->attr('href') : $url;
                    if ($link && strpos($link, 'http') === false) {
                        // Detectar dominio base para enlaces relativos
                        $host = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
                        $link = $host . $link;
                    }

                    $descNode = $node->filter('.text, p, .description');
                    $description = $descNode->count() ? trim($descNode->text()) : '';

                    $locNode = $node->filter('.txt-geo, .location, .place');
                    $location = $locNode->count() ? trim($locNode->text()) : 'Madrid';

                    // Categoría Dinámica
                    $categoryId = $this->detectCategory($title);

                    // Imagen Real o Fallback
                    $imageUrl = $this->extractImage($node, $url);
                    if (!$imageUrl) {
                        $imageUrl = $this->getFallbackImage($categoryId);
                    }

                    $allPlans[] = [
                        'title' => $title,
                        'description' => substr($description, 0, 255),
                        'location' => $location,
                        'price' => 0.0,
                        'date' => date('Y-m-d H:i:s'),
                        'url_source' => $link,
                        'category_id' => $categoryId,
                        'image_url' => $imageUrl
                    ];
                    $pageCount++;
                });

                $this->logger->info("Página {$i}: {$pageCount} planes extraídos.");

                // Ser educados con el servidor
                if ($i < $maxPages) {
                    sleep(1);
                }

            } catch (\Throwable $e) {
                $this->logger->error("Excepción en página {$i}: " . $e->getMessage());
            }
        }

        $this->logger->info("TOTAL SCRAPING FINALIZADO. Planes totales: " . count($allPlans));
        return $allPlans;
    }

    /**
     * Limpia el string del precio para convertirlo en float.
     */
    private function cleanPrice(string $price): float
    {
        // Elimina símbolos de moneda y espacios, cambia coma por punto
        $cleaned = preg_replace('/[^0-9,.]/', '', $price);
        $cleaned = str_replace(',', '.', $cleaned);
        return (float) $cleaned;
    }

    /**
     * Detecta la categoría basada en palabras clave del título.
     * Mapeo:
     * 1: Música (Concierto, Jazz, etc)
     * 2: Teatro (Teatro, Danza)
     * 3: Cine
     * 4: Gastronomía
     * 5: Aire Libre / Varios (Exposiciones, Museo, Arte)
     */
    private function detectCategory(string $title): int
    {
        $titleLower = mb_strtolower($title);

        if (preg_match('/(concierto|música|jazz|rock|banda|vivo)/i', $titleLower)) {
            return 1; // Música
        }
        if (preg_match('/(teatro|danza|drama|comedia|musical)/i', $titleLower)) {
            return 2; // Teatro
        }
        if (preg_match('/(cine|película|estreno|proyección)/i', $titleLower)) {
            return 3; // Cine
        }
        if (preg_match('/(tapa|vino|cata|gastron|ruta|comer)/i', $titleLower)) {
            return 4; // Gastronomía
        }
        if (preg_match('/(exposición|museo|arte|pintura|foto|aire libre|parque)/i', $titleLower)) {
            return 2; // Arte/Museo -> ID 2 (Usando 2 'Teatro' como proxy de Cultura)
        }

        return 1; // Default safe: Música (1)
    }

    /**
     * Extrae la URL de la imagen del nodo.
     */
    private function extractImage(Crawler $node, string $baseUrl): ?string
    {
        // Selectores comunes de imagen en tarjetas
        $imgNode = $node->filter('img')->first();

        if ($imgNode->count() === 0) {
            // Intentar buscar en un div con style background-image (común en webs modernas)
            $styleNode = $node->filter('.image, .thumb, .field-image');
            if ($styleNode->count() > 0) {
                // Lógica compleja omitida por simplicidad, nos centramos en <img>
            }
            return null;
        }

        // Prioridad: data-src (lazy load) > src
        $src = $imgNode->attr('data-src') ?: $imgNode->attr('data-original') ?: $imgNode->attr('src');

        // Validar y normalizar
        if (!$src || strpos($src, 'base64') !== false) {
            return null;
        }

        // URLs relativas
        if (strpos($src, 'http') === false) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?? 'https';
            $host = parse_url($baseUrl, PHP_URL_HOST);
            $src = $scheme . '://' . $host . '/' . ltrim($src, '/');
        }

        return $src;
    }

    /**
     * Devuelve una imagen por defecto basada en la categoría.
     * Usamos URLs de Unsplash source estables por temática.
     */
    private function getFallbackImage(int $categoryId): string
    {
        switch ($categoryId) {
            case 1: // Música
                return 'https://images.unsplash.com/photo-1514525253440-b393452e8d26?auto=format&fit=crop&w=800&q=80';
            case 2: // Teatro/Arte
                return 'https://images.unsplash.com/photo-1503095392237-fa26b21ba375?auto=format&fit=crop&w=800&q=80';
            case 3: // Cine
                return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=800&q=80';
            case 4: // Gastronomía
                return 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=800&q=80';
            default: // Aire Libre/Varios
                return 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=800&q=80';
        }
    }
}