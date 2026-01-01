<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GeoPlans\Core\Router;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Inicializar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Configurar Monolog
$logger = new Logger('geoplans_app');
try {
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::ERROR));
} catch (\Exception $e) {
    // Si falla el log, continuamos pero mostramos error en dev
    error_log("No se pudo iniciar Monolog: " . $e->getMessage());
}

// Configuración básica de rutas (Fase 3: Web y Fase 4: API)
$router = new Router();

// Ruta Web
$router->get('/', [\GeoPlans\Controllers\PlanController::class, 'index']);

// Ruta API
$router->get('/api/plans', [\GeoPlans\Controllers\ApiController::class, 'getPlans']);

// Despachar la petición
try {
    $router->dispatch();
} catch (\Throwable $e) {
    // Loguear el error con Monolog
    $logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);

    http_response_code(500);
    // Página de error amigable
    echo "<h1>Error 500 - Error Interno del Servidor</h1>";
    echo "<p>Ha ocurrido un problema técnico. Por favor, inténtelo más tarde.</p>";
}