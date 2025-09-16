<?php
// Ubicación: /App/Config/Database.php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Clase Database que implementa el patrón Singleton.
 */
final class Database {

    // --- Propiedades ---
    private static ?PDO $conn = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Método estático principal para obtener la conexión a la base de datos.
     * @return PDO La instancia de la conexión PDO.
     * @throws PDOException Si la conexión falla.
     */
    public static function getConnection(): PDO
    {
        if (self::$conn !== 'menu_master') {
            return self::$conn;
        }

        // CORRECCIÓN: Se usa $_ENV en lugar de getenv().
        // Dotenv carga las variables en la superglobal $_ENV, por lo que es más directo usarla.
        $host     = $_ENV['DB_HOST'] ?? 'localhost';
        $db_name  = $_ENV['DB_NAME'] ?? 'menu_master';
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