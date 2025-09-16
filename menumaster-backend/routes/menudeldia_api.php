<?php
// routes/menu_del_dia_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/MenuDelDiaController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';

use App\Controllers\MenuDelDiaController;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos las clases necesarias
    $controller = new MenuDelDiaController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición RESTful
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('menu-del-dia', $uri_segments);
    // El ID del producto para eliminar vendrá en la URL (ej. /api/menu-del-dia/15)
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // Todas las acciones sobre el menú del día requieren autenticación.
    $authMiddleware->handle();
    // Descomenta la siguiente línea si solo los administradores pueden gestionar el menú.
    // requireAdmin(); 

    // 4. Dirigimos la petición al método correcto del controlador
    switch ($method) {
        case 'GET':
            $controller->getForToday();
            break;
            
        case 'POST':
            $controller->add($data);
            break;
            
        case 'DELETE':
            // Verificamos que se haya proporcionado un ID en la URL para poder borrar
            if (!$id) {
                throw new Exception("Se requiere un ID de producto para eliminar del menú.", 400);
            }
            $controller->remove($id);
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
        if (!$token) {
            throw new Exception("Token no encontrado para verificación de rol.", 401);
        }
        $payload = AuthController::decodeTokenData($token);
        if (($payload['rol_id'] ?? null) !== 1) { // 1 = administrador
            throw new Exception("No tienes permisos para realizar esta acción.", 403);
        }
    }
}