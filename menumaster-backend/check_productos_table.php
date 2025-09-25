<?php

// Set environment variables for database connection
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'menu_master';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_CHARSET'] = 'utf8mb4';

require_once 'App/config/conexionDb.php';

use App\Config\ConexionDb;

try {
    $db = ConexionDb::getConnection();
    
    echo "=== PRODUCTOS TABLE STRUCTURE ===\n";
    $stmt = $db->query('DESCRIBE productos');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo $column['Field'] . ' - ' . $column['Type'] . ' - ' . $column['Null'] . ' - ' . $column['Default'] . "\n";
    }
    
    echo "\n=== CHECKING CATEGORIAS TABLE ===\n";
    $stmt = $db->query('SELECT * FROM categorias LIMIT 5');
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($categorias)) {
        echo "❌ No categories found in categorias table\n";
    } else {
        echo "✅ Found categories:\n";
        foreach ($categorias as $cat) {
            echo "  ID: {$cat['id']}, Name: {$cat['nombre']}\n";
        }
    }
    
    echo "\n=== CHECKING ESTADOS_PRODUCTO TABLE ===\n";
    $stmt = $db->query('SELECT * FROM estados_producto');
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($estados)) {
        echo "❌ No product states found in estados_producto table\n";
    } else {
        echo "✅ Found product states:\n";
        foreach ($estados as $estado) {
            echo "  ID: {$estado['id']}, Name: {$estado['nombre']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>