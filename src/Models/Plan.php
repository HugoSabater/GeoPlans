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
    /**
     * Obtiene planes paginados.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll(int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM plans p 
                INNER JOIN categories c ON p.category_id = c.id 
                ORDER BY p.date ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de planes.
     */
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM plans";
        return (int) $this->db->query($sql)->fetchColumn();
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

        // Paginación para filtros también
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int) $filters['limit'];
            $params[':offset'] = (int) $filters['offset'];
        }

        $stmt = $this->db->prepare($sql);

        // Bind manual para enteros (importante para LIMIT/OFFSET)
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : (is_float($value) ? PDO::PARAM_STR : PDO::PARAM_STR);
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Cuenta planes filtrados.
     */
    public function countByFilter(array $filters): int
    {
        $sql = "SELECT COUNT(*) FROM plans p WHERE 1=1";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = (float) $filters['max_price'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Elimina un plan por ID.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM plans WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica si existe un plan por su URL.
     */
    public function exists(string $url): bool
    {
        $sql = "SELECT COUNT(*) FROM plans WHERE url_source = :url";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':url' => $url]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Persiste un nuevo plan.
     *
     * @param array $data
     * @return bool
     */
    public function save(array $data): bool
    {
        $sql = "INSERT INTO plans (title, description, location, price, date, url_source, category_id, image_url) 
                VALUES (:title, :description, :location, :price, :date, :url_source, :category_id, :image_url)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':location' => $data['location'],
            ':price' => $data['price'],
            ':date' => $data['date'],
            ':url_source' => $data['url_source'],
            ':category_id' => $data['category_id'],
            ':image_url' => $data['image_url'] ?? null
        ]);
    }
    /**
     * Elimina planes cuya fecha sea anterior a AHORA.
     */
    public function deleteExpired(): int
    {
        $sql = "DELETE FROM plans WHERE date < NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Vacia la tabla de planes (Reset completo).
     */
    public function truncate(): void
    {
        $sql = "TRUNCATE TABLE plans";
        $this->db->exec($sql);
    }
}