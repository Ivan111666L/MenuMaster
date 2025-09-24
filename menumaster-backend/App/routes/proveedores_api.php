<?php
// routes/proveedores_api.php
require_once BASE_PATH . '/app/Controllers/ProveedorController.php';
use App\Controllers\ProveedorController;

try {
    $controller = new ProveedorController($db);
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $resource_index = array_search('proveedores', $uri_segments);
    $id = isset($uri_segments[$resource_index + 1]) && is_numeric($uri_segments[$resource_index + 1])
        ? (int)$uri_segments[$resource_index + 1]
        : null;
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
            $controller->store($data);
            break;
        case 'PUT':
            if (!$id) throw new Exception("Se requiere un ID de proveedor para actualizar.", 400);
            $controller->update($id, $data);
            break;
        case 'DELETE':
            if (!$id) throw new Exception("Se requiere un ID de proveedor para eliminar.", 400);
            $controller->destroy($id);
            break;
        default:
            throw new Exception("Método no permitido.", 405);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
