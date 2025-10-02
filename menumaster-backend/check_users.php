<?php
// Check users in database
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = App\Config\ConexionDb::getConnection();
    
    // First check table structure
    echo "=== USUARIOS TABLE STRUCTURE ===\n";
    $stmt = $db->query('DESCRIBE usuarios');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Column: {$row['Field']}, Type: {$row['Type']}\n";
    }
    
    echo "\n=== USERS IN DATABASE ===\n";
    $stmt = $db->query('SELECT * FROM usuarios LIMIT 5');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}