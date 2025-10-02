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
    
    // Check detalles_pedido table structure
    echo "\ndetalles_pedido table structure:\n";
    $stmt = $db->query('DESCRIBE detalles_pedido');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Show sample data
    echo "\nSample data from detalles_pedido:\n";
    $stmt = $db->query('SELECT * FROM detalles_pedido LIMIT 3');
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($data)) {
        echo "No data found in detalles_pedido table\n";
    } else {
        foreach($data as $row) {
            echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}