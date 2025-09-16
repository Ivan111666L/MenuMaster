<?php
// routes/movimientos_inventario_api.php

/**
 * Sub-enrutador para los movimientos de inventario.
 */

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/MovimientoInventarioController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';

use App\Controllers\MovimientoInventarioController;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

// --- Lógica del Enrutador ---
try {
    // 1. Instanciamos las clases necesarias
    $controller = new MovimientoInventarioController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Analizamos la petición
    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    // 3. Centralizamos la SEGURIDAD
    // Todas las acciones sobre los movimientos de inventario requieren ser administrador.
    $authMiddleware->handle();
    requireAdmin();

    // 4. Dirigimos la petición al método correcto del controlador
    switch ($method) {
        case 'GET':
            $controller->index();
            break;
        
        case 'POST':
            $controller->store($data);
            break;

        default:
            throw new Exception("Método no permitido para esta ruta.", 405);
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
    function requireAdmin(): void {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) {
            throw new Exception("Token no encontrado para verificación de rol.", 401);
        }
        $payload = AuthController::decodeTokenData($token);
        if (($payload['rol_id'] ?? null) !== 1) { // 1 = administrador
            throw new Exception("No tienes permisos para realizar esta acción.", 403);
        }
    }
}
