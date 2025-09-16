<?php
// Ubicación: App/Config/Database.php

namespace App\Config;

use PDO;
use PDOException;

final class Database {
    private static ?PDO $conn = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Obtiene la única instancia de la conexión a la base de datos (Patrón Singleton).
     * @return PDO La instancia de la conexión PDO.
     * @throws PDOException Si la conexión falla.
     */
    public static function getConnection(): PDO
    {
        if (self::$conn !== null) {
            return self::$conn;
        }

        // Lee las credenciales directamente del .env
        $host     = $_ENV['DB_HOST'] ?? 'localhost';
        $db_name  = $_ENV['DB_NAME'] ?? null;
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        $charset  = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        if (!$db_name) {
            throw new PDOException("La variable de entorno DB_NAME no está configurada.");
        }

        $dsn = "mysql:host={$host};dbname={$db_name};charset={$charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$conn = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            throw new PDOException("Error de conexión con la base de datos.", (int)$e->getCode());
        }

        return self::$conn;
    }
}