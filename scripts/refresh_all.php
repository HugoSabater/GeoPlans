<?php
/**
 * Script de Regeneración Total (TRUNCATE + SCRAPE V2).
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GeoPlans\Core\Database;
use GeoPlans\Models\Plan;
use GeoPlans\Services\ScraperService;
use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 1. Cargar Entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Configurar Logger
$logger = new Logger('refresh');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/refresh.log', Logger::INFO));
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO)); // Ver en consola también

$planModel = new Plan();
$scraper = new ScraperService($logger);

echo "\n--- 🔄 INICIANDO REGENERACIÓN COMPLETA DE BASE DE DATOS ---\n";

// 2. TRUNCATE
try {
    $planModel->truncate();
    $logger->info("✅ Base de datos vaciada (TRUNCATE TABLE plans).");
} catch (\Exception $e) {
    $logger->error("❌ Error vaciando DB: " . $e->getMessage());
    exit;
}

// 3. SCRAPING MULTI-FUENTE
$sources = [
    'TeatroMadrid' => 'https://www.teatromadrid.com/cartelera'
];

$totalInserted = 0;

foreach ($sources as $name => $url) {
    $logger->info("🕷️  Scraping fuente: {$name} ({$url})...");

    // Scrapeamos 3 páginas de cada una
    $plans = $scraper->scrape($url, 3);
    $count = count($plans);

    $logger->info("   -> Encontrados en {$name}: {$count} eventos.");

    // Guardado
    $sourceInserted = 0;
    foreach ($plans as $planData) {
        // Aunque hemos hecho truncate, verificamos por si las webs repiten eventos o scraper lo hace
        if (!$planModel->exists($planData['url_source'])) {
            if ($planModel->save($planData)) {
                $sourceInserted++;
            }
        }
    }
    $logger->info("   -> Guardados en DB de {$name}: {$sourceInserted}");
    $totalInserted += $sourceInserted;
}

echo "\n--------------------------------------------------------------\n";
echo "🎉 PROCESO FINALIZADO.\n";
echo "Total Eventos Reales Insertados: {$totalInserted}\n";
echo "--------------------------------------------------------------\n";
