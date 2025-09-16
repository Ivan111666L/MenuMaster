<?php

// test_connection.php

// 1. Cargar el Autoloader de Composer
// Esto le enseña a PHP dónde encontrar todas tus clases.
require_once __DIR__ . '/vendor/autoload.php';

// 2. Cargar las variables de entorno del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 3. ¡Ahora sí puedes usar tus clases!
use App\Config\Database;
use PDOException;

echo "Intentando conectar a la base de datos...<br>";

try {
    $pdo = Database::getConnection();
    echo "✅ **¡Conexión exitosa!**<br>";

    // Opcional: Verificar la versión del servidor
    $version = $pdo->query('select version()')->fetchColumn();
    echo "Versión de MySQL: " . $version;

} catch (PDOException $e) {
    echo "❌ **Error de conexión:** " . $e->getMessage();
}