<?php
// routes/dashboard_api.php

require_once BASE_PATH . '/App/Controllers/DashboardController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';

use App\Controllers\DashboardController;
use App\Middleware\AuthMiddleware;

try {
    $controller = new DashboardController($db);
    $authMiddleware = new AuthMiddleware();

    // Protegemos la ruta. Solo usuarios logueados pueden acceder.
    $authMiddleware->handle();

    // Por ahora, solo tenemos una ruta: GET /api/dashboard/summary
    // Si en el futuro tienes más (ej. /api/dashboard/detailed-report), las añades aquí.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $controller->getSummary();
        // El controlador ya se encarga de imprimir la respuesta,
        // así que aquí no necesitamos hacer nada con $result.
    } else {
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}