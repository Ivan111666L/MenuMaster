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
    
    echo "🔍 Checking roles table structure:\n";
    echo "=====================================\n";
    
    // Check current table structure
    $stmt = $db->query("DESCRIBE roles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns:\n";
    foreach($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Check if estado_id column exists
    $hasEstadoId = false;
    foreach($columns as $column) {
        if($column['Field'] === 'estado_id') {
            $hasEstadoId = true;
            break;
        }
    }
    
    echo "\n";
    if(!$hasEstadoId) {
        echo "❌ Missing estado_id column. Adding it now...\n";
        
        // Add the missing column
        $db->exec("ALTER TABLE roles ADD COLUMN estado_id INT DEFAULT 1 NOT NULL");
        
        echo "✅ Added estado_id column to roles table\n";
        
        // Update existing roles to have estado_id = 1 (active)
        $db->exec("UPDATE roles SET estado_id = 1 WHERE estado_id IS NULL OR estado_id = 0");
        
        echo "✅ Updated existing roles to have estado_id = 1\n";
    } else {
        echo "✅ estado_id column already exists\n";
    }
    
    // Show current roles
    echo "\nCurrent roles in table:\n";
    $stmt = $db->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($roles as $role) {
        echo "- ID: {$role['id']}, Name: {$role['nombre']}";
        if(isset($role['estado_id'])) {
            echo ", Estado: {$role['estado_id']}";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}