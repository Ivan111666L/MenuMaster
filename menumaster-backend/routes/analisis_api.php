<?php
// routes/analisis_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/AnalisisController.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';

use App\Controllers\AnalisisController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

/**
 * Función de Ayuda para la AUTORIZACIÓN
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token no encontrado.", 401);
        
        $payload = AuthController::decodeTokenData($token);
        
        // Handle both array and object formats for the data property
        $rolId = null;
        if (isset($payload['data'])) {
            if (is_array($payload['data'])) {
                $rolId = $payload['data']['rol_id'] ?? null;
            } elseif (is_object($payload['data'])) {
                $rolId = $payload['data']->rol_id ?? null;
            }
        }
        
        if ($rolId !== 1) { // 1 = administrador
            throw new Exception("Acceso denegado. Se requiere rol de administrador.", 403);
        }
    }
}

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos las clases necesarias
    $controller = new AnalisisController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición RESTful
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('analisis', $uri_segments);
    $action = isset($uri_segments[$resource_index + 1]) ? $uri_segments[$resource_index + 1] : null;

    // 3. Centralizamos la SEGURIDAD
    // Solo administradores pueden acceder a los análisis
    $authMiddleware->handle();
    requireAdmin();

    // 4. Dirigimos la petición al método correcto del controlador
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            case 'ventas':
                $controller->getEstadisticasVentas();
                break;
            
            case 'meseros':
                $controller->getEstadisticasMeseros();
                break;
                
            case 'productos':
                $controller->getEstadisticasProductos();
                break;
                
            case 'pdf':
                $controller->generarPDF();
                break;
                
            default:
                // Si no hay acción específica, mostrar todas las estadísticas
                $controller->getEstadisticasVentas();
                break;
        }
    } else {
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
?>