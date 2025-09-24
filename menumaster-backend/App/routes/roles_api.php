<?php
// routes/roles_api.php

// --- Dependencias ---
require_once BASE_PATH . '/app/Controllers/RolesController.php';
require_once BASE_PATH . '/app/Middleware/AuthMiddleware.php';

use App\Controllers\RolesController;
use App\Middleware\AuthMiddleware;

try {
    // Instanciar el controlador
    $controller = new RolesController();
    
    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $roles_index = array_search('roles', $uri_segments);
    $action = $uri_segments[$roles_index + 1] ?? null;
    
    // Dirigir la petición al método correcto del controlador
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($action) {
                case null:
                case 'list':
                    $controller->getRoles();
                    break;
                    
                case 'by-id':
                    $controller->getRolById();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => "Endpoint '{$action}' no encontrado"]);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'create':
                    $controller->crearRol();
                    break;
                    
                case 'update':
                    $controller->actualizarRol();
                    break;
                    
                case 'delete':
                    $controller->eliminarRol();
                    break;
                    
                case 'assign-permissions':
                    $controller->asignarPermisos();
                    break;
                    
                case 'by-id':
                    $controller->getRolById();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => "Endpoint '{$action}' no encontrado"]);
                    break;
            }
            break;
            
        case 'PUT':
            switch ($action) {
                case 'update':
                    $controller->actualizarRol();
                    break;
                    
                case 'assign-permissions':
                    $controller->asignarPermisos();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => "Endpoint '{$action}' no encontrado"]);
                    break;
            }
            break;
            
        case 'DELETE':
            switch ($action) {
                case 'delete':
                    $controller->eliminarRol();
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
    error_log("Error en roles_api.php: " . $e->getMessage());
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}