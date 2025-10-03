<?php
// API simple para obtener productos para el sistema de pedidos
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../App/config/conexionDb.php';

use App\config\ConexionDb;
use App\Models\ProductoModel;
use App\Models\ProductoIngredientesModel;

try {
    $db = ConexionDb::getConnection();
    $productoModel = new ProductoModel($db);
    $prodIngredientesModel = new ProductoIngredientesModel($db);
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all available products
        $productos = $productoModel->findAll(false); // Solo productos disponibles
        
        // Add ingredients to each product
        foreach ($productos as &$producto) {
            $ingredientes = $prodIngredientesModel->getByProducto($producto['id']);
            $producto['ingredientes'] = $ingredientes;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $productos,
            'total' => count($productos)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>