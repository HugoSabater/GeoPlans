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
     * Scrapea una URL y devuelve un array de planes.
     */
    public function scrape(string $url): array
    {
        $this->logger->info("Iniciando scraping de la URL: {$url}");
        $plans = [];

        try {
            $response = $this->client->get($url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->critical("Error crítico: Status Code {$statusCode} al acceder a {$url}");
                return [];
            }

            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // Selectores para guiadelocio.com/madrid/conciertos
            // Estructura habitual: li.col-md-12 or .list-item
            $nodes = $crawler->filter('li.col-md-12, .list-articulos li, .l-list li');

            $count = $nodes->count();
            $this->logger->info("Encontrados {$count} eventos en GuiaDelOcio");

            if ($count === 0) {
                // Fallback genérico a cualquier cosa que parezca una tarjeta
                $nodes = $crawler->filter('.card, article, .item, .news-item');
                $count = $nodes->count();
                $this->logger->info("Intento fallback genérico: {$count} elementos encontrados");
            }

            if ($count === 0) {
                $this->logger->warning("No se encontraron elementos. Guardando debug.");
                file_put_contents(__DIR__ . '/../../logs/debug_scrape.html', $html);
            }

            $nodes->slice(0, 15)->each(function (Crawler $node) use (&$plans, $url) {
                // Título
                $titleNode = $node->filter('h2, h3, .title');
                $title = $titleNode->count() ? trim($titleNode->text()) : '';

                // Si no tiene título, lo saltamos
                if (!$title)
                    return;

                // Link
                $linkNode = $node->filter('h2 a, h3 a, .title a');
                $link = $linkNode->count() ? $linkNode->attr('href') : $url;
                if ($link && strpos($link, 'http') === false) {
                    $link = 'https://www.guiadelocio.com' . $link;
                }

                // Descripción
                $descNode = $node->filter('.text, p, .description');
                $description = $descNode->count() ? trim($descNode->text()) : '';

                // Ubicación (Recinto)
                $locNode = $node->filter('.txt-geo, .location, .place');
                $location = $locNode->count() ? trim($locNode->text()) : 'Madrid';

                // Fecha
                $dateNode = $node->filter('.fecha, .date');
                // A veces la fecha está en el título o descripción, usamos NOW si falla
                $date = date('Y-m-d H:i:s');

                // Categoría Dinámica
                $categoryId = $this->detectCategory($title);

                $plans[] = [
                    'title' => $title,
                    'description' => substr($description, 0, 255), // Truncar por seguridad DB
                    'location' => $location,
                    'price' => 0.0,
                    'date' => $date,
                    'url_source' => $link,
                    'category_id' => $categoryId
                ];
            });

            $this->logger->info("Scraping finalizado. Insertados: " . count($plans));

        } catch (\Throwable $e) {
            $this->logger->error("Error durante el scraping: " . $e->getMessage());
        }

        return $plans;
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
            return 2; // Arte/Museo -> ID 2 (Usando 2 'Teatro' como proxy de Cultura, o 1 si se prefiere)
        }

        return 1; // Default safe: Música (1) para evitar error FK si el 5 no existe
    }
}