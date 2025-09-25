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
    
    echo "🔍 Checking email column in usuarios table:\n";
    echo "==========================================\n";
    
    // Check if email column exists
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasEmail = false;
    foreach($columns as $column) {
        if($column['Field'] === 'email') {
            $hasEmail = true;
            break;
        }
    }
    
    if(!$hasEmail) {
        echo "❌ Missing email column. Adding it now...\n";
        
        // Add the missing column
        $db->exec("ALTER TABLE usuarios ADD COLUMN email VARCHAR(255) UNIQUE NOT NULL AFTER nombre");
        
        echo "✅ Added email column to usuarios table\n";
    } else {
        echo "✅ Email column already exists\n";
    }
    
    // Show updated table structure
    echo "\nUpdated table structure:\n";
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}