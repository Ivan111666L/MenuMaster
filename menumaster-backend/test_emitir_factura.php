<?php
// Test: emitir factura electrónica (stub) para un pedido

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Config/ConexionDb.php';
require_once __DIR__ . '/App/Controllers/FacturacionElectronicaController.php';
require_once __DIR__ . '/App/models/PedidoModel.php';

use App\Config\ConexionDb;
use App\Controllers\FacturacionElectronicaController;
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
        throw new Exception('No se pudo conectar a BD');
    }
    echo "✅ BD OK\n";

    // Tomar el último pedido (ya facturado o no)
    $stmt = $db->prepare("SELECT id FROM pedidos ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "❌ No hay pedidos para emitir factura electrónica\n";
        exit(0);
    }
    $pedidoId = (int)$row['id'];
    echo "Pedido: #{$pedidoId}\n";

    $controller = new FacturacionElectronicaController($db);
    $controller->emitirFactura(['pedido_id' => $pedidoId, 'email' => 'cliente@example.com']);
    echo "\n✅ Factura electrónica emitida (stub) para pedido #{$pedidoId}\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}