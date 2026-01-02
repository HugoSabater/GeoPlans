<?php
require_once __DIR__ . '/../vendor/autoload.php';
use GeoPlans\Core\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();

$newCats = ['Teatro', 'Cine', 'Aire Libre'];

foreach ($newCats as $cat) {
    try {
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute([':name' => $cat]);
        echo "Insertada: $cat\n";
    } catch (PDOException $e) {
        // Ignorar si existe (aunque nombre no es Unique usualmente, asumimos)
        echo "Error insertando $cat: " . $e->getMessage() . "\n";
    }
}

$all = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
print_r($all);
