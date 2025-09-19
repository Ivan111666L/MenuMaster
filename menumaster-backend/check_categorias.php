<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use app\config\ConexionDb;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    
    echo "Estructura de la tabla categorías:\n";
    $stmt = $db->query('DESCRIBE categorias');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\nDatos actuales:\n";
    $stmt = $db->query('SELECT * FROM categorias');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: {$row['id']} | Nombre: {$row['nombre']} | Descripción: " . ($row['descripcion'] ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>