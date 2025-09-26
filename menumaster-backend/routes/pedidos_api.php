<?php
// routes/pedidos_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/PedidoController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php'; // Necesario para requireAdmin

use App\Controllers\PedidoController;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos clases
    $controller = new PedidoController($db);
    $authMiddleware = new AuthMiddleware();
    
    // 2. Seguridad: Todas las rutas de pedidos están protegidas
    $authMiddleware->handle();

    // 3. Analizamos la URL RESTful
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('pedidos', $uri_segments);
    
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;
    $action = $uri_segments[$resource_index + 2] ?? null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 4. Dirigimos al método del controlador
    switch ($method) {
        case 'GET':
            // CORRECCIÓN: El controlador envía la respuesta, no necesitamos capturar un resultado.
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        
        case 'POST':
            if ($id && $action === 'facturar') {
                $controller->facturar($id, $data);
            } else {
                $controller->store($data);
            }
            break;
            
        case 'PUT':
            if ($id && $action === 'estado') {
                $controller->updateStatus($id, $data);
            } else {
                // Para una actualización general de un pedido (si la implementas)
                // if ($id) { $controller->update($id, $data); } else ...
                throw new Exception("Ruta PUT no válida. Usa /pedidos/{id}/estado.", 404);
            }
            break;

        case 'DELETE':
            // La eliminación es una acción sensible, aplicamos autorización de admin
            requireAdmin();
            if (!$id) throw new Exception("Se requiere un ID para eliminar.", 400);
            $controller->destroy($id);
            break;
            
        default:
            throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    // 5. Capturador de Errores Centralizado
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Función de Ayuda para la AUTORIZACIÓN (si decides usarla)
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token no encontrado para verificación de rol.", 401);
        
        $payload = AuthController::decodeTokenData($token);
        if (($payload['rol_id'] ?? null) !== 1) { // 1 = administrador
            throw new Exception("No tienes permisos para realizar esta acción.", 403);
        }
    }

    switch ($action) {
    case 'toma-pedido-data':
        if ($requestMethod === 'GET') {
            // Llama a la función del controlador que obtiene estos datos
            // $pedidoController->getTomaPedidoData(); 
            // POR AHORA, PARA PROBAR, PON UNA RESPUESTA SIMPLE:
            http_response_code(200);
            echo json_encode(['message' => 'Ruta toma-pedido-data funciona!']);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido.']);
        }
        break;

    default:
        // Si no hay acción específica, permitir el flujo normal del switch principal
        break;
    }
}