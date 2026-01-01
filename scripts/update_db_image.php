<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GeoPlans\Core\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();

try {
    // Add image_url column if it doesn't exist
    $sql = "ALTER TABLE plans ADD COLUMN image_url VARCHAR(512) DEFAULT NULL AFTER category_id";
    $db->exec($sql);
    echo "Columna 'image_url' añadida con éxito.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "La columna 'image_url' ya existe.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
