<?php
// routes/inventario_api.php

/**
 * Sub-enrutador para el recurso de inventario (ingredientes y su stock).
 */

// --- Dependencias ---
// CORRECCIÓN: Se usa el controlador correcto para gestionar el inventario.
require_once BASE_PATH . '/App/Controllers/IngredienteController.php'; 
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';

use App\Controllers\IngredienteController;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos las clases necesarias
    $controller = new IngredienteController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición de forma robusta
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('inventario', $uri_segments);
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // La gestión de inventario requiere autenticación.
    $authMiddleware->handle(); 
    // Solo los administradores pueden modificar el inventario.
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
            // Aquí se crearía un nuevo ingrediente en el inventario
            $controller->store($data);
            break;

        case 'PUT':
            // Aquí se actualizaría un ingrediente (ej. cambiar stock mínimo)
            if (!$id) throw new Exception("Se requiere un ID para actualizar.", 400);
            $controller->update($id, $data);
            break;

        case 'DELETE':
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
    // CORRECCIÓN: Se añade 'success' => false para consistencia
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}


/**
 * Función de Ayuda para la AUTORIZACIÓN
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) {
            throw new Exception("No se encontró el token para la verificación de rol.", 401);
        }
        $payload = AuthController::decodeTokenData($token);
        if (($payload['rol_id'] ?? null) !== 1) { // 1 = administrador
            throw new Exception("No tienes permisos para realizar esta acción.", 403);
        }
    }
}
