<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    echo "Intentando conectar a MySQL con los siguientes parámetros:\n";
    echo "Host: $host\n";
    echo "Database: $dbname\n";
    echo "User: $user\n";
    echo "Password: [" . ($pass ? "SET" : "NOT SET") . "]\n\n";

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "¡Conexión exitosa a la base de datos!\n";
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}
