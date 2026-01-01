<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GeoPlans\Core\Database;
use GeoPlans\Services\ScraperService;
use GeoPlans\Models\Plan;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

// Inicialización de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Configurar Logger
$logger = new Logger('scraper');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/scraper.log', Logger::DEBUG));

$logger->info("--- INICIO DE PROCESO CLI ---");

// Instanciar dependencias
$scraper = new ScraperService($logger);
$planModel = new Plan();

// URL de prueba (Cambiar por una real o un archivo local de prueba)
// Para propósitos de este ejercicio, supongamos que el sitio tiene esta estructura.
$targetUrl = "https://www.ejemplo-de-eventos.com/conciertos";

$results = $scraper->scrape($targetUrl);

$inserted = 0;
foreach ($results as $item) {
    if ($planModel->save($item)) {
        $inserted++;
    }
}

$logger->info("Proceso terminado. Insertados {$inserted} nuevos planes.");
echo "Proceso finalizado con éxito. Revisa logs/scraper.log para más detalles.\n";