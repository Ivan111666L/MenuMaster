<?php
// routes/auth.php

// --- Dependencias ---
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Models/UsuarioModel.php';
require_once BASE_PATH . '/app/Models/RolModel.php';

use app\Controllers\AuthController;
use app\Models\UsuarioModel;
use app\Models\RolModel;

try {
    // CORRECCIÓN: Instanciamos los modelos primero...
    $usuarioModel = new UsuarioModel($db);
    $rolModel = new RolModel($db);
    
    // ...y luego los pasamos al constructor del controlador.
    $controller = new AuthController($db, $usuarioModel, $rolModel);

    // 2. Analizamos la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $auth_index = array_search('auth', $uri_segments);
    $action = $uri_segments[$auth_index + 1] ?? null;
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Dirigimos la petición al método correcto del controlador
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'register':
                // CORRECCIÓN: El controlador ahora maneja la respuesta. Solo lo llamamos.
                $controller->register($data);
                break;

            case 'login':
                $controller->login($data);
                break;
            
            case 'forgot-password':
                $controller->forgotPassword($data);
                break;

            case 'reset-password':
                $controller->resetPassword($data);
                break;

            default:
                throw new Exception("Ruta de autenticación no encontrada.", 404);
        }
    } else {
        throw new Exception("Método no permitido para esta ruta.", 405);
    }

} catch (Exception $e) {
    // Capturador de Errores Centralizado
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}