<?php

declare(strict_types=1);

namespace GeoPlans\Models;

use GeoPlans\Core\Database;
use PDO;

/**
 * Modelo Plan para la gestión de datos de planes de ocio.
 */
class Plan
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los planes con su nombre de categoría.
     *
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM plans p 
                INNER JOIN categories c ON p.category_id = c.id 
                ORDER BY p.date ASC";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Filtra planes por categoría y/o precio máximo.
     *
     * @param array $filters ['category_id' => int, 'max_price' => float]
     * @return array
     */
    public function getByFilter(array $filters): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM plans p 
                INNER JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";

        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = (float) $filters['max_price'];
        }

        $sql .= " ORDER BY p.date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Persiste un nuevo plan.
     *
     * @param array $data
     * @return bool
     */
    public function save(array $data): bool
    {
        $sql = "INSERT INTO plans (title, description, location, price, date, url_source, category_id) 
                VALUES (:title, :description, :location, :price, :date, :url_source, :category_id)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':location' => $data['location'],
            ':price' => $data['price'],
            ':date' => $data['date'],
            ':url_source' => $data['url_source'],
            ':category_id' => $data['category_id']
        ]);
    }
}