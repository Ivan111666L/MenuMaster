<?php
// routes/mesas_api.php

// --- Dependencias ---
require_once BASE_PATH . '/app/Controllers/MesaController.php';
require_once BASE_PATH . '/app/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';

use app\Controllers\MesaController;
use app\Middleware\AuthMiddleware;
use app\Controllers\AuthController;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos las clases necesarias
    $controller = new MesaController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición RESTful
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('mesas', $uri_segments);
    
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;
    $action = $uri_segments[$resource_index + 1] ?? null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // MEJORA: Se protegen TODAS las rutas de mesas.
    $authMiddleware->handle();

    // Las acciones que modifican datos (POST, PUT, DELETE) requieren ser administrador.
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        requireAdmin();
    }

    // 4. Dirigimos la petición al método correcto del controlador
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->show($id);
            } else if ($action === 'disponibles') {
                $controller->disponibles();
            } else {
                $controller->index();
            }
            break;
        
        case 'POST':
            if ($action === 'reset') { // Corresponde a POST /api/mesas/reset
                $controller->resetAll();
            } else {
                $controller->store($data);
            }
            break;
        
        case 'PUT':
            if (!$id) {
                throw new Exception("Se requiere un ID de mesa para actualizar.", 400);
            }
            $controller->update($id, $data);
            break;
            
        case 'DELETE':
            if (!$id) {
                throw new Exception("Se requiere un ID de mesa para eliminar.", 400);
            }
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
 * Función de Ayuda para la AUTORIZACIÓN
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) {
            throw new Exception("Token no encontrado para verificación de rol.", 401);
        }
        $payload = AuthController::decodeTokenData($token);
        if (($payload['rol_id'] ?? null) !== 1) { // 1 = administrador
            throw new Exception("No tienes permisos para realizar esta acción.", 403);
        }
    }
}