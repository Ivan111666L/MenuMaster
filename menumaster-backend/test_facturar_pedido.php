<?php
// Test script: facturar pedido y guardar en historial

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Config/ConexionDb.php';
require_once __DIR__ . '/App/config/Constantes.php';
require_once __DIR__ . '/App/Controllers/PedidoController.php';
require_once __DIR__ . '/App/models/PedidoModel.php';
require_once __DIR__ . '/App/models/MesaModel.php';

use App\Config\ConexionDb;
use App\Controllers\PedidoController;
use Dotenv\Dotenv;

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

    // Seleccionar un pedido no facturado
    $stmt = $db->prepare("SELECT p.id FROM pedidos p LEFT JOIN estados_pedido ep ON p.estado_id = ep.id WHERE ep.nombre != 'facturado' ORDER BY p.id DESC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "❌ No hay pedidos disponibles para facturar.\n";
        exit(0);
    }
    $pedidoId = (int)$row['id'];
    echo "Pedido seleccionado: #{$pedidoId}\n";

    $controller = new PedidoController($db);
    // Simular datos de pago
    $data = [ 'metodo_pago' => 'efectivo', 'dividir' => false, 'personas' => 1 ];
    $controller->facturar($pedidoId, $data);
    echo "✅ Flujo de facturación ejecutado para pedido #{$pedidoId}\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}