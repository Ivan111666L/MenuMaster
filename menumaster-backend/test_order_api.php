<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';
require_once 'App/Controllers/PedidoController.php';

use App\Config\ConexionDb;
use App\Controllers\PedidoController;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    echo "Database connected successfully\n";
    
    // Test PedidoController instantiation
    $pedidoController = new PedidoController($db);
    echo "PedidoController instantiated successfully\n";
    
    // Test getting all orders
    echo "\n=== Testing GET all orders ===\n";
    ob_start();
    $pedidoController->index();
    $output = ob_get_clean();
    echo "Response: " . $output . "\n";
    
    // Test getting orders by status
    echo "\n=== Testing GET orders by status ===\n";
    $_GET['estado'] = 'pendiente';
    ob_start();
    $pedidoController->index();
    $output = ob_get_clean();
    echo "Response: " . $output . "\n";
    
    // Clean up
    unset($_GET['estado']);
    
    echo "\nOrder API tests completed!\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}