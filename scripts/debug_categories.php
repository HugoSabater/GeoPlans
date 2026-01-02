<?php
require_once __DIR__ . '/../vendor/autoload.php';
use GeoPlans\Core\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM categories");
$cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($cats);
