<?php

declare(strict_types=1);

namespace GeoPlans\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Clase Database que implementa el patrón Singleton para la conexión PDO.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Constructor privado para evitar instanciación externa.
     */
    private function __construct()
    {
    }

    /**
     * Retorna la instancia única de la conexión PDO.
     *
     * @return PDO
     * @throws RuntimeException Si la conexión falla.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                    $_ENV['DB_HOST'],
                    $_ENV['DB_PORT'],
                    $_ENV['DB_NAME']
                );

                self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // En producción, esto debería ir al log vía Monolog
                throw new RuntimeException("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}