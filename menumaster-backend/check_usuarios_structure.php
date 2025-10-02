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
    
    // Check usuarios table structure
    echo "\nusuarios table structure:\n";
    $stmt = $db->query('DESCRIBE usuarios');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Show sample data
    echo "\nSample data from usuarios:\n";
    $stmt = $db->query('SELECT * FROM usuarios LIMIT 3');
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($data)) {
        echo "No data found in usuarios table\n";
    } else {
        foreach($data as $row) {
            echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    // Check if roles table exists
    echo "\nChecking roles table:\n";
    $stmt = $db->query("SHOW TABLES LIKE 'roles'");
    $rolesExists = $stmt->fetch();
    
    if ($rolesExists) {
        echo "✅ roles table exists\n";
        $stmt = $db->query('DESCRIBE roles');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "❌ roles table does not exist\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}