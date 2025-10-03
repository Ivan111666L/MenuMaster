<?php
// routes/notificaciones_api.php

require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Controllers/AuthController.php';

use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

try {
    $authMiddleware = new AuthMiddleware();
    // Requiere estar autenticado
    $authMiddleware->handle();

    // Parsear la acción desde la URL
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', trim($request_uri, '/'));
    $idx = array_search('notificaciones', $segments);
    $action = $segments[$idx + 1] ?? null; // Ej: {id} o 'leida'

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        // Listar notificaciones
        try {
            $stmt = $db->query("SELECT id, mensaje, COALESCE(leida, 0) AS leida, COALESCE(created_at, NOW()) AS created_at FROM notificaciones ORDER BY created_at DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            return;
        } catch (Throwable $e) {
            // Si la tabla no existe u otro error, devolver lista vacía para no romper el frontend
            error_log('[notificaciones] GET error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }
    }

    if ($method === 'POST') {
        // Marcar notificación como leída: /api/notificaciones/{id}/leida
        // Obtener segmentos finales para detectar patrón {id}/leida
        $last = $segments[$idx + 2] ?? null; // podría ser 'leida'
        $maybeId = $segments[$idx + 1] ?? null;

        if ($last === 'leida' && is_numeric($maybeId)) {
            $id = intval($maybeId);
            try {
                $stmt = $db->prepare("UPDATE notificaciones SET leida = 1 WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
                return;
            } catch (Throwable $e) {
                error_log('[notificaciones] POST leida error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo marcar como leída']);
                return;
            }
        }

        throw new Exception('Ruta POST no válida para notificaciones.', 404);
    }

    throw new Exception('Método HTTP no permitido.', 405);

} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}