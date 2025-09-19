<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use app\config\ConexionDb;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    
    // Actualizar categorías existentes para que tengan los nombres correctos
    $updates = [
        2 => ['nombre' => 'Platos Fuertes', 'descripcion' => 'Platos principales y especialidades'],
        4 => ['nombre' => 'Bebidas', 'descripcion' => 'Bebidas calientes, frías y alcohólicas']
    ];
    
    foreach ($updates as $id => $data) {
        $stmt = $db->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$data['nombre'], $data['descripcion'], $id]);
        echo "✅ Categoría ID {$id} actualizada a '{$data['nombre']}'\n";
    }
    
    // Mostrar categorías actuales
    echo "\nCategorías disponibles:\n";
    $stmt = $db->query("SELECT id, nombre, descripcion FROM categorias ORDER BY 
        CASE 
            WHEN nombre = 'Entradas' THEN 1
            WHEN nombre = 'Platos Fuertes' THEN 2  
            WHEN nombre = 'Bebidas' THEN 3
            WHEN nombre = 'Postres' THEN 4
            ELSE 5
        END, nombre");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: {$row['id']} | {$row['nombre']} | {$row['descripcion']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>