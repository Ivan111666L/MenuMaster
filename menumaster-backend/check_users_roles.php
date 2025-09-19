<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use app\config\ConexionDb;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    
    echo "=== ROLES DISPONIBLES ===\n";
    $stmt = $db->query('SELECT * FROM roles ORDER BY id');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['id']}: {$row['nombre']}\n";
    }
    
    echo "\n=== ESTADOS GENERALES ===\n";
    $stmt = $db->query('SELECT * FROM estados_generales ORDER BY id');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['id']}: {$row['nombre']}\n";
    }
    
    echo "\n=== USUARIOS CON ROLES Y ESTADOS ===\n";
    $stmt = $db->query("SELECT 
        u.id, u.nombre, u.email, 
        r.nombre AS rol,
        e.nombre AS estado
    FROM usuarios u
    LEFT JOIN roles r ON u.rol_id = r.id
    LEFT JOIN estados_generales e ON u.estado_id = e.id
    ORDER BY u.nombre");
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['nombre']} ({$row['email']}) - Rol: {$row['rol']}, Estado: {$row['estado']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>