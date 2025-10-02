<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/config/ConexionDb.php';

use App\Config\ConexionDb;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    
    echo "=== PERMISOS TABLE STRUCTURE ===\n";
    $stmt = $db->prepare('DESCRIBE permisos');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $column) {
        echo $column['Field'] . ' - ' . $column['Type'] . "\n";
    }
    
    echo "\n=== SAMPLE PERMISOS DATA ===\n";
    $stmt = $db->prepare('SELECT * FROM permisos LIMIT 5');
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($permisos as $permiso) {
        echo json_encode($permiso) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}