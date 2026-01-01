<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GeoPlans\Models\Plan;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// 1. Instanciar Modelo
$planModel = new Plan();

echo "--- VERIFICACIÓN DE LIMPIEZA ---\n";

// 2. Insertar Planes de prueba (Uno pasado, uno futuro)
$planModel->save([
    'title' => 'Evento Pasado',
    'description' => 'Test',
    'location' => 'Madrid',
    'price' => 0,
    'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
    'url_source' => 'http://test.com',
    'category_id' => 1
]);

$planModel->save([
    'title' => 'Evento Futuro',
    'description' => 'Test',
    'location' => 'Madrid',
    'price' => 10,
    'date' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'url_source' => 'http://test.com',
    'category_id' => 1
]);

$countBefore = $planModel->countAll();
echo "Total antes de limpieza: $countBefore\n";

// 3. Ejecutar deleteExpired()
$deleted = $planModel->deleteExpired();
echo "Eliminados (expirados): $deleted\n";
echo "Total después de limpieza: " . $planModel->countAll() . "\n";

// 4. Ejecutar truncate() (comentado por seguridad, descomentar para probar)
// $planModel->truncate();
// echo "Total después de truncate: " . $planModel->countAll() . "\n";

echo "--- TEST FINALIZADO ---\n";
