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
                'User-Agent' => 'Mozilla/5.0 (GeoPlans Scraper 1.0)'
            ]
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
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // Ejemplo: Buscamos elementos con clase .event-item
            // Ajustar selectores según la web elegida
            $crawler->filter('.event-item')->each(function (Crawler $node) use (&$plans, $url) {
                $title = $node->filter('h2')->count() ? $node->filter('h2')->text() : 'Sin título';
                $location = $node->filter('.location')->count() ? $node->filter('.location')->text() : 'Ubicación no especificada';
                $priceRaw = $node->filter('.price')->count() ? $node->filter('.price')->text() : '0';

                $plans[] = [
                    'title' => trim($title),
                    'location' => trim($location),
                    'price' => $this->cleanPrice($priceRaw),
                    'date' => date('Y-m-d H:i:s'), // O extraer de la web si es posible
                    'url_source' => $url,
                    'category_id' => 1, // Por defecto, asignamos una categoría ID 1
                    'description' => 'Extraído automáticamente mediante scraping.'
                ];
            });

            $this->logger->info("Scraping finalizado. Encontrados " . count($plans) . " planes.");

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
}