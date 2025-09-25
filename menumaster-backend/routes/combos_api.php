<?php
// routes/combos_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/ComboController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Middleware/RolMiddleware.php';

use App\Controllers\ComboController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;

try {
    // Instanciar el controlador y middleware
    $comboController = new ComboController($db);
    $authMiddleware = new AuthMiddleware();
    $rolMiddleware = new RolMiddleware();
    
    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $combos_index = array_search('combos', $uri_segments);
    $action = $uri_segments[$combos_index + 1] ?? null;
    $id = $uri_segments[$combos_index + 2] ?? null;
    
    // Dirigir la petición al método correcto del controlador
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Verificar autenticación para todas las rutas GET
            $authMiddleware->requireAuth();
            
            if ($action === null || $action === 'list') {
                // GET /combos - Obtener todos los combos
                $comboController->getCombos();
            } elseif (is_numeric($action)) {
                // GET /combos/{id} - Obtener combo específico
                $comboController->getCombo((int)$action);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "Endpoint no encontrado"]);
            }
            break;
            
        case 'POST':
            // Verificar autenticación y permisos de administrador/gerente
            $authMiddleware->requireAuth();
            if (!$rolMiddleware->checkAnyRole(['administrador', 'gerente'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para crear combos']);
                exit;
            }
            
            if ($action === null || $action === 'create') {
                // POST /combos - Crear nuevo combo
                $comboController->createCombo();
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "Endpoint no encontrado"]);
            }
            break;
            
        case 'PUT':
            // Verificar autenticación y permisos de administrador/gerente
            $authMiddleware->requireAuth();
            if (!$rolMiddleware->checkAnyRole(['administrador', 'gerente'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para actualizar combos']);
                exit;
            }
            
            if (is_numeric($action)) {
                // PUT /combos/{id} - Actualizar combo
                $comboController->updateCombo((int)$action);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "ID de combo requerido para actualizar"]);
            }
            break;
            
        case 'PATCH':
            // Verificar autenticación y permisos de administrador/gerente
            $authMiddleware->requireAuth();
            if (!$rolMiddleware->checkAnyRole(['administrador', 'gerente'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para cambiar estado de combos']);
                exit;
            }
            
            if (is_numeric($action) && $id === 'status') {
                // PATCH /combos/{id}/status - Cambiar estado del combo
                $comboController->changeComboStatus((int)$action);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "Ruta no válida para cambio de estado"]);
            }
            break;
            
        case 'DELETE':
            // Verificar autenticación y permisos de administrador/gerente
            $authMiddleware->requireAuth();
            if (!$rolMiddleware->checkAnyRole(['administrador', 'gerente'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para eliminar combos']);
                exit;
            }
            
            if (is_numeric($action)) {
                // DELETE /combos/{id} - Eliminar combo
                $comboController->deleteCombo((int)$action);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "ID de combo requerido para eliminar"]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en combos_api.php: " . $e->getMessage());
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}