<?php
// routes/analisis_api.php

// --- Dependencias ---
require_once BASE_PATH . '/app/Controllers/AnalisisController.php';
require_once BASE_PATH . '/app/Middleware/AuthMiddleware.php';

use app\Controllers\AnalisisController;
use app\Middleware\AuthMiddleware;

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
    // 5. Capturador de Errores Centralizado
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Función de Ayuda para la AUTORIZACIÓN
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token no encontrado.", 401);
        
        $payload = app\Controllers\AuthController::decodeTokenData($token);
        $rol = $payload['rol'] ?? null;
        
        if ($rol !== 'administrador') {
            throw new Exception("Acceso denegado. Se requiere rol de administrador.", 403);
        }
    }
}
?>