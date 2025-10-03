<?php
// routes/proveedores_api.php
require_once BASE_PATH . '/App/Controllers/ProveedorController.php';
use App\Controllers\ProveedorController;

try {
    $controller = new ProveedorController($db);
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('proveedores', $uri_segments);
    // Parseo de segmentos para soportar acciones personalizadas
    $segment_after = $uri_segments[$resource_index + 1] ?? null;
    $second_after = $uri_segments[$resource_index + 2] ?? null;
    $id = (isset($segment_after) && is_numeric($segment_after)) ? (int)$segment_after : null;
    $action = (isset($segment_after) && !is_numeric($segment_after)) ? $segment_after : ($second_after ?? null);
    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        case 'POST':
            // Soporta POST /api/proveedores/{id}/asociar-ingrediente
            if ($id && $action === 'asociar-ingrediente') {
                $ingredienteId = isset($data['ingrediente_id']) ? (int)$data['ingrediente_id'] : null;
                if (!$ingredienteId) {
                    throw new Exception("Se requiere 'ingrediente_id' para asociar.", 400);
                }
                $controller->asociarIngrediente($id, $ingredienteId);
            } else {
                $controller->store($data);
            }
            break;
        case 'PUT':
            if (!$id) throw new Exception("Se requiere un ID de proveedor para actualizar.", 400);
            $controller->update($id, $data);
            break;
        case 'DELETE':
            // Soporta DELETE /api/proveedores/desasociar-ingrediente/{ingrediente_id}
            if (($segment_after === 'desasociar-ingrediente') && isset($second_after) && is_numeric($second_after)) {
                $ingredienteId = (int)$second_after;
                $controller->desasociarIngrediente($ingredienteId);
            } else {
                if (!$id) throw new Exception("Se requiere un ID de proveedor para eliminar.", 400);
                $controller->destroy($id);
            }
            break;
        default:
            throw new Exception("Método no permitido.", 405);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
