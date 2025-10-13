<?php
// routes/permisos_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/PermisosController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';

use App\Controllers\PermisosController;
use App\Middleware\AuthMiddleware;

try {
    // Instanciar dependencias
    $controller = new PermisosController();
    $auth = new AuthMiddleware();
    
    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $permisos_index = array_search('permisos', $uri_segments);
    $action = $uri_segments[$permisos_index + 1] ?? null;
    
    // Dirigir la petición al método correcto del controlador
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($action) {
                case null:
                case 'list':
                    // Requiere autenticación básica; el controlador validará permisos específicos
                    $auth->requireAuth();
                    $controller->getPermisos();
                    break;
                    
                case 'by-role':
                    $auth->requireAuth();
                    $controller->getPermisosByRol();
                    break;
                    
                case 'current-user':
                    // Debe requerir autenticación para obtener permisos del usuario actual
                    $auth->requireAuth();
                    $controller->getCurrentUserPermisos();
                    break;
                    
                case 'check':
                    $auth->requireAuth();
                    $controller->checkPermiso();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => "Endpoint '{$action}' no encontrado"]);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'assign':
                    $auth->requireAuth();
                    $controller->asignarPermisos();
                    break;
                    
                case 'create':
                    $auth->requireAuth();
                    $controller->crearPermiso();
                    break;
                    
                case 'by-role':
                    $auth->requireAuth();
                    $controller->getPermisosByRol();
                    break;
                    
                case 'check':
                    $auth->requireAuth();
                    $controller->checkPermiso();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => "Endpoint '{$action}' no encontrado"]);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en permisos_api.php: " . $e->getMessage());
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
