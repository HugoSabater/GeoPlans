<?php
/**
 * Script de Mantenimiento de GeoPlans.
 * 
 * Uso: php scripts/maintenance.php
 * Cron: 0 3 * * * (Ejecutar a las 3 AM)
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

// 2. Configurar Logger exclusivo para mantenimiento
$logPath = __DIR__ . '/../logs/maintenance.log';
$logger = new Logger('maintenance');
$logger->pushHandler(new StreamHandler($logPath, Logger::INFO));

$logger->info('=== INICIO MANTENIMIENTO: ' . date('Y-m-d H:i:s') . ' ===');

try {
    // 3. Instancias
    $planModel = new Plan();
    $scraper = new ScraperService($logger); // Reutilizamos logger o pasar uno específico

    // ----------------------------------------------------
    // PASO 1: LIMPIEZA
    // ----------------------------------------------------
    $deleted = $planModel->deleteExpired();
    $logger->info("PASO 1: Limpieza completada. Eventos expirados eliminados: {$deleted}");
    echo "✔ Limpieza: {$deleted} eliminados.\n";

    // ----------------------------------------------------
    // PASO 2: ADQUISICIÓN (Scraping)
    // ----------------------------------------------------
    // Target: TeatroMadrid
    $url = 'https://www.teatromadrid.com/cartelera';
    $logger->info("PASO 2: Iniciando Scraping (Target: {$url})");

    // Scraping de 5 páginas
    $scrapedPlans = $scraper->scrape($url, 5);
    $totalScraped = count($scrapedPlans);
    $logger->info("Scraping finalizado. Planes recuperados: {$totalScraped}");
    echo "✔ Scraping: {$totalScraped} encontrados.\n";

    // ----------------------------------------------------
    // PASO 3: GUARDADO (Filtrando duplicados)
    // ----------------------------------------------------
    $inserted = 0;
    $skipped = 0;

    foreach ($scrapedPlans as $planData) {
        // Verificar si ya existe por URL
        if ($planModel->exists($planData['url_source'])) {
            $skipped++;
            continue;
        }

        // Intentar guardar
        if ($planModel->save($planData)) {
            $inserted++;
        }
    }

    $logger->info("PASO 3: Guardado completado. Insertados: {$inserted}. Ignorados (Duplicados): {$skipped}");
    echo "✔ Base de Datos: {$inserted} insertados, {$skipped} duplicados.\n";

} catch (\Throwable $e) {
    $logger->error("CRITICAL ERROR en mantenimiento: " . $e->getMessage());
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

$logger->info('=== FIN MANTENIMIENTO: ' . date('Y-m-d H:i:s') . ' ===');
echo "✔ Mantenimiento completado con éxito.\n";
