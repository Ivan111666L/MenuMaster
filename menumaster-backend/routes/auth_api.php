<?php
// routes/auth.php

/**
 * Sub-enrutador para la autenticación.
 */

use App\Controllers\AuthController;

require_once BASE_PATH . '/App/Controllers/AuthController.php';

try {
    // 1. Instanciamos el controlador
    $controller = new AuthController($db);

    // 2. Analizamos la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $auth_index = array_search('auth', $uri_segments);
    $action = $uri_segments[$auth_index + 1] ?? null;

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Dirigimos la petición al método correcto
    if ($method === 'POST') {
        switch ($action) {
            case 'register':
                $result = $controller->register($data);
                http_response_code(201); // Created
                // MEJORA: Respuesta JSON consistente
                echo json_encode(['success' => true, 'data' => $result]);
                break;

            case 'login':
                $result = $controller->login($data);
                http_response_code(200); // OK
                // MEJORA: Respuesta JSON consistente
                echo json_encode(['success' => true, 'data' => $result]);
                break;
            
            case 'forgot-password':
                $result = $controller->forgotPassword($data);
                http_response_code(200); // OK
                echo json_encode(['success' => true, 'data' => $result]);
                break;

            case 'reset-password':
                $result = $controller->resetPassword($data);
                http_response_code(200); // OK
                echo json_encode(['success' => true, 'data' => $result]);
                break;

            default:
                throw new Exception("Ruta de autenticación no encontrada.", 404);
        }
    } else {
        throw new Exception("Método no permitido para esta ruta.", 405);
    }

} catch (Exception $e) {
    // Capturador de Errores Centralizado
    $code = $e->getCode() ?: 400; // Por defecto, Bad Request
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}