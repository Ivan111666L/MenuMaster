Claro. Tu archivo de rutas para los ingredientes tiene la lógica básica, pero le faltan las capas de seguridad y manejo de errores que son cruciales y que ya hemos implementado en el resto de tu API.

He corregido el archivo completo para que siga el mismo patrón robusto y profesional que tus otros enrutadores.

routes/ingredientes_api.php (Corregido)
PHP

<?php
// routes/ingredientes_api.php

// --- Dependencias ---
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

    // 2. Analizamos la petición RESTful de forma robusta
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('ingredientes', $uri_segments);
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // Las acciones que modifican el inventario (POST, PUT, DELETE) requieren ser administrador.
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        $authMiddleware->handle();
        requireAdmin();
    } elseif ($method === 'GET') {
        // Permitimos que cualquier usuario logueado vea los ingredientes.
        $authMiddleware->handle();
    }


    // 4. Dirigimos la petición al método correcto del controlador
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        
        case 'POST':
            $controller->store($data);
            break;
        
        case 'PUT':
            if (!$id) {
                throw new Exception("Se requiere un ID de ingrediente para actualizar.", 400);
            }
            $controller->update($id, $data);
            break;
            
        case 'DELETE':
            if (!$id) {
                throw new Exception("Se requiere un ID de ingrediente para eliminar.", 400);
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
 * Función de Ayuda para la AUTORIZACIÓN (debe estar disponible para los routers que la necesiten)
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