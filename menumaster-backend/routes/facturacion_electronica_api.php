<?php
// routes/facturacion_electronica_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/FacturacionElectronicaController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';

use App\Controllers\FacturacionElectronicaController;
use App\Middleware\AuthMiddleware;

try {
    $authMiddleware = new AuthMiddleware();
    $authMiddleware->requireAuth();
    $controller = new FacturacionElectronicaController($db);

    $request_uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments  = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('facturacion-electronica', $uri_segments);
    $action        = $uri_segments[$resource_index + 1] ?? null;
    $method        = $_SERVER['REQUEST_METHOD'];
    $data          = json_decode(file_get_contents('php://input'), true) ?? [];

    switch ($method) {
        case 'POST':
            switch ($action) {
                case 'emitir':
                    // Body esperado: { pedido_id, email (opcional) }
                    $controller->emitirFactura($data);
                    break;
                case 'enviar-correo':
                    // Body esperado: { pedido_id, email }
                    $controller->enviarFacturaPorCorreo($data);
                    break;
                default:
                    throw new Exception("Acción POST '{$action}' no válida para facturación electrónica.", 404);
            }
            break;
        case 'GET':
            switch ($action) {
                case 'estado':
                    // Query esperado: ?pedido_id=123
                    $pedidoId = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : null;
                    $controller->obtenerEstadoFactura($pedidoId);
                    break;
                default:
                    throw new Exception("Acción GET '{$action}' no válida para facturación electrónica.", 404);
            }
            break;
        default:
            throw new Exception("Método '{$method}' no soportado para facturación electrónica.", 405);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}