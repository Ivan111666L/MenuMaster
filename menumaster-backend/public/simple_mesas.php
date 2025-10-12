<?php
// API simple para obtener mesas disponibles para el sistema de pedidos
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
// Asegurar carga de constantes de la aplicación
require_once __DIR__ . '/../App/config/Constantes.php';
require_once __DIR__ . '/../App/config/conexionDb.php';

use App\Config\ConexionDb;
use App\Models\MesaModel;

try {
    $db = ConexionDb::getConnection();
    $mesaModel = new MesaModel($db);
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Obtener todas las mesas disponibles
        $mesas = $mesaModel->findDisponibles();

        // Normalizar estructura de salida
        $mesas = $mesas ?: [];
        echo json_encode([
            'success' => true,
            'data' => $mesas,
            'total' => count($mesas)
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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