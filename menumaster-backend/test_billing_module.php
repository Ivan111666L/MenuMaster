<?php
// Test script for billing module: facturarPedido and mesa state release

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Config/ConexionDb.php';
require_once __DIR__ . '/App/models/PedidoModel.php';
require_once __DIR__ . '/App/models/MesaModel.php';

use App\Config\ConexionDb;
use App\Models\PedidoModel;
use App\Models\MesaModel;
use Dotenv\Dotenv;
use PDO;

try {
    // Define BASE_PATH for models that rely on it
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', __DIR__);
    }
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Database connection
    $db = ConexionDb::getConnection();
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }
    echo "✅ Database connected successfully\n";

    // Instantiate models
    $pedidoModel = new PedidoModel($db);
    $mesaModel = new MesaModel($db);

    echo "\n=== Testing Billing Module ===\n";

    // Find an order ready to invoice (not already facturado)
    $stmt = $db->prepare("SELECT p.id, p.mesa_id, ep.nombre AS estado
                          FROM pedidos p
                          LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                          WHERE ep.nombre != 'facturado'
                          ORDER BY p.id DESC
                          LIMIT 1");
    $stmt->execute();
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo "❌ No orders found to invoice\n";
        exit(0);
    }

    echo "1. Selected order for invoicing: #{$pedido['id']} (mesa {$pedido['mesa_id']}, estado {$pedido['estado']})\n";

    // Ensure required estado 'facturado' exists
    $checkEstadoStmt = $db->prepare("SELECT id FROM estados_pedido WHERE nombre = 'facturado'");
    $checkEstadoStmt->execute();
    $facturadoEstado = $checkEstadoStmt->fetch(PDO::FETCH_ASSOC);
    if (!$facturadoEstado) {
        $insertEstadoStmt = $db->prepare("INSERT INTO estados_pedido (nombre) VALUES ('facturado')");
        $insertEstadoStmt->execute();
        echo "   - Added missing estado 'facturado' to estados_pedido\n";
    }

    // Perform invoicing (simulate by marking as 'facturado' and freeing table)
    echo "2. Invoicing order (simulate state change and mesa release)...\n";
    $ok = $pedidoModel->actualizarEstadoPedido((int)$pedido['id'], 'facturado');
    if (!$ok) {
        echo "❌ Failed to invoice order\n";
        exit(1);
    }
    echo "✅ Order invoiced successfully\n";

    // Free up table (mesa) after invoicing
    $mesaFreed = $mesaModel->cambiarEstado((int)$pedido['mesa_id'], 'disponible');
    if (!$mesaFreed) {
        echo "❌ Failed to set mesa to disponible\n";
        exit(1);
    }

    // Verify order state changed to 'facturado'
    $stmt2 = $db->prepare("SELECT ep.nombre AS estado
                           FROM pedidos p
                           LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                           WHERE p.id = :id");
    $stmt2->bindParam(':id', $pedido['id'], PDO::PARAM_INT);
    $stmt2->execute();
    $estadoActual = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "3. Current order state: " . ($estadoActual['estado'] ?? 'unknown') . "\n";

    // Verify mesa state changed to 'disponible'
    $stmtMesa = $db->prepare("SELECT em.nombre AS estado_mesa
                               FROM mesas m
                               LEFT JOIN estados_mesa em ON m.estado_id = em.id
                               WHERE m.id = :mesa_id");
    $stmtMesa->bindParam(':mesa_id', $pedido['mesa_id'], PDO::PARAM_INT);
    $stmtMesa->execute();
    $estadoMesa = $stmtMesa->fetch(PDO::FETCH_ASSOC);
    echo "4. Mesa state after invoicing: " . ($estadoMesa['estado_mesa'] ?? 'unknown') . "\n";

    echo "\n=== Billing Module Testing Complete ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}