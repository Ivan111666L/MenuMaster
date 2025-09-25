<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/CuadreDiarioController.php';

use App\Controllers\CuadreDiarioController;

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

// Verificar si el usuario tiene permisos de administrador
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'gerente') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

// Crear instancia del controlador
$controller = new CuadreDiarioController($db);

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$baseUri = '/MenuMaster/menumaster-backend/cuadre_diario';
$route = str_replace($baseUri, '', $requestUri);
$route = strtok($route, '?');

// Manejar las rutas
header('Content-Type: application/json');

switch ($route) {
    case '':
    case '/':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo json_encode($controller->getCuadresDiarios());
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo json_encode($controller->crearOActualizarCuadreDiario());
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        }
        break;
        
    case (preg_match('/^\/(\d+)$/', $route, $matches) ? true : false):
        $id = $matches[1];
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo json_encode($controller->getCuadreDiario($id));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        }
        break;
        
    case '/rentabilidad-productos':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo json_encode($controller->getRentabilidadProductos());
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        }
        break;
        
    case '/resumen-ventas':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo json_encode($controller->getResumenVentasDiarias());
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        }
        break;
        
    case '/inventario-proveedores':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo json_encode($controller->getInventarioConProveedores());
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
        break;
}