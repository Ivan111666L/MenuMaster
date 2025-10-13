<?php
// routes/usuarios_api.php

/**
 * Sub-enrutador para el recurso de usuarios.
 */

// --- Dependencias ---
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/UsuarioController.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';
require_once BASE_PATH . '/App/Utils/AuthHelpers.php';

// Usar alias para las clases
use App\Controllers\UsuarioController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos el controlador y el middleware
    $controller = new UsuarioController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición
    $method = $_SERVER['REQUEST_METHOD'];
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('usuarios', $uri_segments);
    
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : ($uri_segments[$resource_index + 1] ?? null);
    
    $action = $uri_segments[$resource_index + 2] ?? null;
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // Todas las rutas de usuarios requieren autenticación
    $authMiddleware->handle();
    
    // Las acciones de escritura y ver la lista completa de usuarios requieren ser administrador.
    if (in_array($method, ['POST', 'PUT', 'DELETE']) || ($method === 'GET' && $id === null)) {
        \App\Utils\AuthHelpers::requireAdmin();
    }

    // 4. Dirigimos la petición al método correcto
    switch ($method) {
        case 'GET':
            if ($id === 'perfil') {
                $controller->getProfile();
            } elseif (is_numeric($id)) {
                $controller->show($id);
            } elseif ($id === null) {
                $controller->index();
            } else {
                throw new Exception("Ruta de usuario no encontrada.", 404);
            }
            break;

        case 'POST':
            $controller->store($data);
            break;

        case 'PUT':
            if (!is_numeric($id)) {
                throw new Exception("Se requiere un ID de usuario numérico para actualizar.", 400);
            }
            if ($action === 'desactivar') {
                $controller->deactivate($id);
            } else {
                $controller->update($id, $data);
            }
            break;

        case 'DELETE':
            if (!is_numeric($id)) {
                throw new Exception("Se requiere un ID de usuario numérico para eliminar.", 400);
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


// Helper de autorización: usar la implementación centralizada
// La verificación de admin se delega a AuthHelpers::requireAdmin() para evitar problemas de alcance
