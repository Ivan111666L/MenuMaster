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
    
    // Check if menu_del_dia table exists
    $stmt = $db->query("SHOW TABLES LIKE 'menu_del_dia'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "\nmenu_del_dia table exists\n";
        
        // Show table structure
        echo "\nTable structure:\n";
        $stmt = $db->query('DESCRIBE menu_del_dia');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        
        // Show sample data
        echo "\nSample data:\n";
        $stmt = $db->query('SELECT * FROM menu_del_dia LIMIT 3');
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($data)) {
            echo "No data found in menu_del_dia table\n";
        } else {
            foreach($data as $row) {
                echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
            }
        }
    } else {
        echo "\nmenu_del_dia table does NOT exist\n";
        
        // Show all tables
        echo "\nAvailable tables:\n";
        $stmt = $db->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach($tables as $table) {
            echo "- $table\n";
        }
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}