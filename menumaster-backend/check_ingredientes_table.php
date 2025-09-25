<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/config/conexionDb.php';

use App\Config\ConexionDb;

try {
    $db = ConexionDb::getConnection();
    $stmt = $db->query('DESCRIBE ingredientes');
    
    echo "=== INGREDIENTES TABLE STRUCTURE ===\n";
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}