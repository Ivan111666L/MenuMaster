<?php
use app\config\ConexionDb;
use app\Controllers\AuthController;

require_once BASE_PATH . '/app/Utils/Validator.php';
require_once BASE_PATH . '/app/config/ConexionDb.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';

try {
    // Conexión BD
    $db = ConexionDb::getConnection();
    $authController = new AuthController($db);

    // Método HTTP
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Partes de la ruta
    $basePath   = dirname($_SERVER['SCRIPT_NAME']);
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $route = $requestUri;
    if (strpos($requestUri, $basePath) === 0) {
        $route = substr($requestUri, strlen($basePath));
    }

    $route_parts = explode('/', trim($route, '/'));

    // Verificar que empiece con /api
    if (($route_parts[0] ?? '') !== 'api') {
        throw new Exception("El endpoint solicitado no es parte de la API.", 404);
    }

    // Segundo segmento = recurso (ej: auth, productos, pedidos, etc.)
    $resource = $route_parts[1] ?? null;
    $action   = $route_parts[2] ?? null;

    switch ($resource) {
        case 'auth':
            switch ($action) {
                case 'register':
                    if ($requestMethod === 'POST') {
                        $authController->register();
                    }
                    break;
                case 'login':
                    if ($requestMethod === 'POST') {
                        $authController->login();
                    }
                    break;
                case 'verify':
                    if ($requestMethod === 'GET') {
                        $authController->verifyToken();
                    }
                    break;
                default:
                    throw new Exception("Acción '{$action}' no válida para auth.", 404);
            }
            break;

        case 'productos':
            require_once BASE_PATH . '/routes/productos_api.php';
            break;
        case 'pedidos':
            require_once BASE_PATH . '/routes/pedidos_api.php';
            break;
        // ...otros recursos
        default:
            throw new Exception("Recurso '{$resource}' no válido.", 404);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}