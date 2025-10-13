<?php
// test_pagos_model.php
// Prueba sencilla del modelo de Pagos para validar creación directa sin pasar por la API.

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Config/ConexionDb.php';
require_once __DIR__ . '/App/Models/PagosModel.php';

use App\Config\ConexionDb;
use App\Models\PagosModel;
use Dotenv\Dotenv;
use PDO;

try {
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', __DIR__);
    }
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $db = ConexionDb::getConnection();
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    echo "✅ Conexión a BD OK\n";

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Asegurar carpeta de logs para evitar warnings del modelo
    $logsDir = BASE_PATH . '/App/logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0777, true);
    }

    $pagos = new PagosModel($db);
    // Ajusta estos valores según datos válidos en tu BD
    $pagos->monto = 100.00;
    // Buscar un método de pago válido, crear 'Efectivo' si no existe
    $stmtMetodo = $db->prepare("SELECT id FROM metodos_pago ORDER BY id ASC LIMIT 1");
    $stmtMetodo->execute();
    $rowMetodo = $stmtMetodo->fetch(PDO::FETCH_ASSOC);
    if (!$rowMetodo) {
        $stmtCreateMetodo = $db->prepare("INSERT INTO metodos_pago (nombre) VALUES ('Efectivo')");
        $stmtCreateMetodo->execute();
        $pagos->metodo_pago_id = (int)$db->lastInsertId();
    } else {
        $pagos->metodo_pago_id = (int)$rowMetodo['id'];
    }
    // Tomar un pedido existente (el último)
    $stmt = $db->prepare("SELECT id FROM pedidos ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pagos->pedido_id = $row ? (int)$row['id'] : 0; // permitir 0 si no hay pedidos
    $pagos->usuario_id = 0;     // Usuario sistema/prueba
    // Buscar un usuario válido para cumplir FK (fallback al primero)
    $stmtUsuario = $db->prepare("SELECT id FROM usuarios ORDER BY id ASC LIMIT 1");
    $stmtUsuario->execute();
    $rowUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    if ($rowUsuario) {
        $pagos->usuario_id = (int)$rowUsuario['id'];
    }

    if ($pagos->crear()) {
        echo json_encode(['success' => true, 'message' => 'Pago creado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fallo al crear el pago']);
        echo "\n";
        // Intento directo para diagnosticar el error
        try {
            $stmtDirect = $db->prepare("INSERT INTO pagos (pedido_id, monto, metodo_pago_id, usuario_id) VALUES (:pedido_id, :monto, :metodo_pago_id, :usuario_id)");
            $stmtDirect->bindValue(':pedido_id', $pagos->pedido_id, PDO::PARAM_INT);
            $stmtDirect->bindValue(':monto', $pagos->monto);
            $stmtDirect->bindValue(':metodo_pago_id', $pagos->metodo_pago_id, PDO::PARAM_INT);
            $stmtDirect->bindValue(':usuario_id', $pagos->usuario_id, PDO::PARAM_INT);
            $stmtDirect->execute();
            echo "Insert directo OK\n";
        } catch (Throwable $e2) {
            echo "Diagnóstico SQL: " . $e2->getMessage() . "\n";
        }
    }
    echo "\n";

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
    echo "\n";
}

?>