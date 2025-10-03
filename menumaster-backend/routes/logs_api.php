<?php
// routes/logs_api.php

require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/models/UserActivityLogModel.php';

use App\Middleware\AuthMiddleware;
use App\Models\UserActivityLogModel;

try {
    $authMiddleware = new AuthMiddleware();
    // Requiere autenticación
    $authMiddleware->handle();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        throw new Exception('Método HTTP no permitido.', 405);
    }

    // Filtros opcionales vía query string
    $filtros = [];
    if (isset($_GET['user_id'])) $filtros['user_id'] = intval($_GET['user_id']);
    if (isset($_GET['action'])) $filtros['action'] = $_GET['action'];
    if (isset($_GET['status'])) $filtros['status'] = $_GET['status'];
    if (isset($_GET['fecha_inicio'])) $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
    if (isset($_GET['fecha_fin'])) $filtros['fecha_fin'] = $_GET['fecha_fin'];
    if (isset($_GET['limit'])) $filtros['limit'] = intval($_GET['limit']);
    if (isset($_GET['offset'])) $filtros['offset'] = intval($_GET['offset']);

    $model = new UserActivityLogModel($db);
    $logs = $model->getActividades($filtros);

    echo json_encode(['success' => true, 'data' => $logs]);
    return;

} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}