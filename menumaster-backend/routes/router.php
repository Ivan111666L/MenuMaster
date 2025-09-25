<?php
use App\Config\ConexionDb;
use App\Controllers\AuthController;

require_once BASE_PATH . '/app/Utils/Validator.php';
require_once BASE_PATH . '/app/config/ConexionDb.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/App/Utils/AuthHelpers.php';

try {
    // Conexión BD
    $db = ConexionDb::getConnection();
    $authController = new AuthController($db);

    // Método HTTP
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Partes de la ruta
    $basePath   = dirname($_SERVER['SCRIPT_NAME']);
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

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
                // case 'verify':
                //     if ($requestMethod === 'GET') {
                //         $authController->verifyToken();
                //     }
                    // break;
                default:
                    throw new Exception("Acción '{$action}' no válida para auth.", 404);
            }
            break;
            
        case 'pedidos':
            require_once BASE_PATH . '/routes/pedidos_api.php';
            break;
        case 'productos':
            require_once BASE_PATH . '/routes/productos_api.php';
            break;
        case 'categorias':
            require_once BASE_PATH . '/routes/categoria_api.php';
            break;
        case 'dashboard':
            require_once BASE_PATH . '/routes/dashboard_api.php';
            break;
        case 'ingredientes':
            require_once BASE_PATH . '/routes/ingredientes_api.php';
            break;
        case 'inventario':
            require_once BASE_PATH . '/routes/inventario_api.php';
            break;
        case 'menudeldia':
            require_once BASE_PATH . '/routes/menudeldia_api.php';
            break;
        case 'movimientosinventario':
            require_once BASE_PATH . '/routes/movimientosinventario_api.php';
            break;
        case 'usuarios':
            require_once BASE_PATH . '/routes/usuarios_api.php';
            break;
        case 'mesas':
            require_once BASE_PATH . '/routes/mesas_api.php';
            break;
        case 'analisis':
            require_once BASE_PATH . '/routes/analisis_api.php';
            break;
        case 'proveedores':
            require_once BASE_PATH . '/routes/proveedores_api.php';
            break;
        case 'combos':
            require_once BASE_PATH . '/routes/combos_api.php';
            break;
        case 'roles':
            require_once BASE_PATH . '/routes/roles_api.php';
            break;
        case 'permisos':
            require_once BASE_PATH . '/routes/permisos_api.php';
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