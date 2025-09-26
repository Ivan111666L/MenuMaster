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

    // Solo se permite el método GET para este endpoint.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // CORRECCIÓN: Se elimina la asignación a '$result'.
        // El método 'getSummary' del controlador ya se encarga de enviar la respuesta y terminar el script.
        $controller->getSummary();
    } else {
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
