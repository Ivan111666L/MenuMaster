<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use App\Config\ConexionDb;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    echo "Database connected successfully\n";
    
    // Check if required tables exist
    $tables = ['pedidos', 'detalles_pedido', 'estados_pedido', 'mesas', 'productos', 'usuarios'];
    foreach ($tables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE '$table'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "Table '$table' exists\n";
        } else {
            echo "ERROR: Table '$table' does not exist\n";
        }
    }
    
    // Check estados_pedido data
    $stmt = $db->prepare('SELECT * FROM estados_pedido');
    $stmt->execute();
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Estados pedido available: " . count($estados) . "\n";
    foreach ($estados as $estado) {
        echo "- " . $estado['nombre'] . "\n";
    }
    
    // Check mesas data
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM mesas');
    $stmt->execute();
    $mesasCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Mesas available: " . $mesasCount['count'] . "\n";
    
    // Check productos data
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM productos');
    $stmt->execute();
    $productosCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Productos available: " . $productosCount['count'] . "\n";
    
    // Check usuarios data
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM usuarios');
    $stmt->execute();
    $usuariosCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Usuarios available: " . $usuariosCount['count'] . "\n";
    
    echo "\nSystem ready for order testing!\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}