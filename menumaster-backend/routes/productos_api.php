<?php
// routes/productos_api.php

/**
 * Sub-enrutador para el recurso de productos.
 */

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/ProductoController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';

use App\Controllers\ProductoController;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos las clases necesarias
    $controller = new ProductoController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición RESTful
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('productos', $uri_segments);
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // CORRECCIÓN: Se añade protección también para el método GET.
    $authMiddleware->handle(); // Verifica que el token sea válido para todas las acciones.
    
    // Las acciones que modifican datos requieren ser administrador.
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        requireAdmin();
    }

    // 4. Dirigimos la petición al método correcto del controlador
    switch ($method) {
        case 'GET':
            // CORRECCIÓN: El controlador maneja su propia respuesta. El router solo lo llama.
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;

        case 'POST':
            // CORRECCIÓN: Se eliminó la lógica redundante '?:'.
            $controller->store($data);
            break;

        case 'PUT':
            if (!$id) throw new Exception("Se requiere un ID de producto para actualizar.", 400);
            $controller->update($id, $data);
            break;

        case 'DELETE':
            if (!$id) throw new Exception("Se requiere un ID de producto para eliminar.", 400);
            $controller->destroy($id);
            break;

        default:
            throw new Exception("Método no permitido.", 405);
    }

    // CORRECCIÓN: El bloque para enviar la respuesta de éxito se elimina,
    // ya que cada método del controlador ahora se encarga de eso.

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