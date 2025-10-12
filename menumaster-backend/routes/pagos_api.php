<?php
// routes/pagos_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/AuthController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Models/MetodoPagoModel.php';
require_once BASE_PATH . '/App/Models/PagosModel.php';

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

// Helper de autorización similar a otros módulos
if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) {
            throw new Exception("Token no encontrado para verificación de rol.", 401);
        }

        // Decodificar token y obtener rol desde la propiedad 'data'
        $payload = AuthController::decodeTokenData($token);

        $rolId = null;
        if (isset($payload['data'])) {
            if (is_array($payload['data'])) {
                $rolId = $payload['data']['rol_id'] ?? null;
            } elseif (is_object($payload['data'])) {
                $rolId = $payload['data']->rol_id ?? null;
            }
        }

        if ($rolId !== 1) { // 1 = administrador
            throw new Exception("No tienes permisos para realizar esta acción.", 403);
        }
    }
}

try {
    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $pagos_index = array_search('pagos', $uri_segments);
    $action = $uri_segments[$pagos_index + 1] ?? null; // 'metodos' o null

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Instanciar modelos
    $metodosModel = new \App\Models\MetodoPagoModel($db);
    // CORRECCIÓN: Usar la clase correcta del modelo de pagos
    $pagosModel = new \App\Models\PagosModel($db);

    if ($method === 'GET') {
        if ($action === 'metodos') {
            // Listar métodos de pago
            // Protegemos con autenticación básica (rol admin) para gestionar catálogo
            requireAdmin();
            $stmt = $metodosModel->read();
            $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $metodos]);
            return;
        }

        // Listar pagos
        requireAdmin();
        $data = $pagosModel->leer();
        echo json_encode(['success' => true, 'data' => $data]);
        return;
    }

    if ($method === 'POST') {
        if ($action === 'metodos') {
            // Crear método de pago
            requireAdmin();
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $nombre = trim($input['nombre'] ?? '');
            if ($nombre === '') {
                throw new Exception('El nombre del método es obligatorio.', 422);
            }

            // Insertar directamente (el modelo actual no define create)
            $stmt = $db->prepare("INSERT INTO metodos_pago (nombre) VALUES (:nombre)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->execute();
            $id = $db->lastInsertId();
            echo json_encode(['success' => true, 'data' => ['id' => (int)$id, 'nombre' => $nombre]]);
            return;
        }

        // Crear pago general
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $monto = $input['monto'] ?? null;
        $metodo_id = $input['metodo_id'] ?? null;
        $pedido_id = $input['pedido_id'] ?? null; // opcional

        if ($monto === null || $metodo_id === null) {
            throw new Exception('Campos requeridos: monto, metodo_id.', 422);
        }

        // Verificar método de pago existente
        $stmtMetodo = $db->prepare('SELECT id FROM metodos_pago WHERE id = :id');
        $stmtMetodo->bindParam(':id', $metodo_id, PDO::PARAM_INT);
        $stmtMetodo->execute();
        $metodo = $stmtMetodo->fetch(PDO::FETCH_ASSOC);
        if (!$metodo) {
            throw new Exception('Método de pago no encontrado.', 404);
        }

        // Rellenar modelo
        $pagosModel->monto = $monto;
        $pagosModel->metodo_pago_id = (int)$metodo_id;
        $pagosModel->pedido_id = $pedido_id ? (int)$pedido_id : 0; // permitir 0 si no hay pedido

        // Tomar usuario del token (id dentro de 'data')
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        $usuarioId = null;
        if ($token) {
            $payload = AuthController::decodeTokenData($token);
            if (isset($payload['data'])) {
                if (is_array($payload['data'])) {
                    $usuarioId = $payload['data']['id'] ?? null;
                } elseif (is_object($payload['data'])) {
                    $usuarioId = $payload['data']->id ?? null;
                }
            }
        }
        $pagosModel->usuario_id = $usuarioId ?? 0;

        if ($pagosModel->crear()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No se pudo crear el pago.', 500);
        }
        return;
    }

    throw new Exception('Método HTTP no permitido.', 405);

} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}