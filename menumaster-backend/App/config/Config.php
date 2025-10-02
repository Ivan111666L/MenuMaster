<?php
namespace App\Config;

use PDO;
use PDOException;

class Config {
    private static $dbHost = "localhost";
    private static $dbName = "menumaster";
    private static $dbUser = "root";
    private static $dbPass = "";

    public static function getConnection() {
        try {
            $pdo = new PDO(
                "mysql:host=" . self::$dbHost . ";dbname=" . self::$dbName . ";charset=utf8",
                self::$dbUser,
                self::$dbPass
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getJwtConfig() {
        return [
            "secret" => $_ENV['JWT_SECRET_KEY'] ?? "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.MenuMaster2024!", // Use env variable or fallback
            "algorithm" => "HS256",
            "expiration" => (int)($_ENV['JWT_EXPIRES_SECONDS'] ?? 3600) // 1 hora
        ];
    }
}
