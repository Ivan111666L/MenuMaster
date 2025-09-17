<?php
// routes/router.php

/**
 * Enrutador Principal de la API.
 */

use app\config\conexionDb;
require_once BASE_PATH . '/app/Utils/Validator.php';

require_once BASE_PATH . '/app/config/conexionDb.php';

// --- Lógica Central de Enrutamiento ---
try {
    // Obtenemos la única instancia de la conexión a la BD
    $db = conexionDb::getConnection();

    // --- Análisis de URL Robusto ---
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    $route = $requestUri;
    if (strpos($requestUri, $basePath) === 0) {
        $route = substr($requestUri, strlen($basePath));
    }
    
    $route_parts = explode('/', trim($route, '/'));

    if (($route_parts[0] ?? '') !== 'api') {
        throw new Exception("El endpoint solicitado no es parte de la API.", 404);
    }
    
    $main_resource = $route_parts[1] ?? null;

    // --- Derivación al Sub-Enrutador Correspondiente ---
    switch ($main_resource) {
        case 'auth':
            require_once BASE_PATH . '/routes/auth_api.php';
            break;
        case 'usuarios':
            require_once BASE_PATH . '/routes/usuarios_api.php';
            break;
        case 'productos':
            require_once BASE_PATH . '/routes/productos_api.php';
            break;
        case 'pedidos':
            require_once BASE_PATH . '/routes/pedidos_api.php';
            break;
        case 'inventario':
            require_once BASE_PATH . '/routes/inventario_api.php';
            break;
        case 'dashboard':
            require_once BASE_PATH . '/routes/dashboard_api.php';
            break;
        case 'menu-del-dia':
            require_once BASE_PATH . '/routes/menu_del_dia_api.php';
            break;
        case 'categorias':
            require_once BASE_PATH . '/routes/categorias_api.php';
            break;
        case 'mesas':
            require_once BASE_PATH . '/routes/mesas_api.php';
            break;
        default:
            throw new Exception("Recurso no encontrado en la API.", 404);
    }

} catch (Exception $e) {
    // Capturador de Errores Global
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}