<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GeoPlans\Controllers\ApiController;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Capture output
ob_start();
$controller = new ApiController();
$controller->getPlans();
$output = ob_get_clean();

$data = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "ERROR: Invalid JSON\n";
    exit(1);
}

if (!isset($data['status']) || $data['status'] !== 'success') {
    echo "ERROR: Status not success\n";
    print_r($data);
    exit(1);
}

if (!isset($data['data']) || !is_array($data['data'])) {
    echo "ERROR: Missing 'data' array\n";
    exit(1);
}

echo "API OK. Found " . count($data['data']) . " plans.\n";
if (count($data['data']) > 0) {
    $first = $data['data'][0];
    echo "Sample Plan:\n";
    echo " - Title: " . $first['title'] . "\n";
    echo " - Category: " . ($first['category_name'] ?? 'MISSING') . "\n";
    echo " - URL: " . ($first['url_source'] ?? 'MISSING') . "\n";
}
