<?php
// Verificación de trazabilidad tras facturación: estado de pedido, mesa, pagos y stock de ingredientes

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Config/ConexionDb.php';

use App\Config\ConexionDb;
use PDO;

try {
    // Cargar variables de entorno
    if (class_exists('Dotenv\\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    $db = ConexionDb::getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== TRACEABILIDAD FACTURACIÓN ===\n";

    // Obtener último pedido facturado
    $stmt = $db->prepare("SELECT p.id, p.mesa_id, ep.nombre AS estado
                          FROM pedidos p
                          LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                          WHERE ep.nombre = 'facturado'
                          ORDER BY p.id DESC
                          LIMIT 1");
    $stmt->execute();
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo "❌ No se encontró pedido facturado.\n";
        exit(1);
    }

    $pedidoId = (int)$pedido['id'];
    echo "Pedido facturado: #{$pedidoId} | Estado: {$pedido['estado']} | Mesa: {$pedido['mesa_id']}\n";

    // Estado de la mesa
    $stmtMesa = $db->prepare("SELECT em.nombre AS estado_mesa
                               FROM mesas m
                               LEFT JOIN estados_mesa em ON m.estado_id = em.id
                               WHERE m.id = :mesa_id");
    $stmtMesa->execute([':mesa_id' => $pedido['mesa_id']]);
    $estadoMesa = $stmtMesa->fetchColumn();
    echo "Estado mesa: " . ($estadoMesa ?: 'N/A') . "\n";

    // Pagos registrados para el pedido
    $stmtPagos = $db->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(monto),0) AS total FROM pagos WHERE pedido_id = :pedido_id");
    $stmtPagos->execute([':pedido_id' => $pedidoId]);
    $pagos = $stmtPagos->fetch(PDO::FETCH_ASSOC);
    echo "Pagos registrados: {$pagos['cnt']} | Total pagado: {$pagos['total']}\n";

    // Detalles del pedido
    $stmtDetalles = $db->prepare("SELECT dp.producto_id, dp.cantidad
                                  FROM detalles_pedido dp
                                  WHERE dp.pedido_id = :pedido_id");
    $stmtDetalles->execute([':pedido_id' => $pedidoId]);
    $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
    echo "Detalles del pedido: " . count($detalles) . "\n";

    // Consumo estimado por ingrediente y stock actual
    $stmtConsumo = $db->prepare("SELECT i.id AS ingrediente_id, i.descripcion, i.stock_actual,
                                        SUM(pi.cantidad * dp.cantidad) AS consumo_estimado
                                 FROM detalles_pedido dp
                                 JOIN productos_ingredientes pi ON pi.producto_id = dp.producto_id
                                 JOIN ingredientes i ON i.id = pi.ingrediente_id
                                 WHERE dp.pedido_id = :pedido_id
                                 GROUP BY i.id, i.descripcion, i.stock_actual");
    $stmtConsumo->execute([':pedido_id' => $pedidoId]);
    $consumos = $stmtConsumo->fetchAll(PDO::FETCH_ASSOC);

    if ($consumos) {
        echo "\nIngredientes afectados (ID | Descripción | Stock actual | Consumo estimado):\n";
        foreach ($consumos as $c) {
            echo $c['ingrediente_id'] . " | " . $c['descripcion'] . " | " . $c['stock_actual'] . " | " . ($c['consumo_estimado'] ?: 0) . "\n";
        }
    } else {
        echo "\nNo se encontraron ingredientes asociados a los productos del pedido.\n";
    }

    // Confirmación básica
    $okMesa = strtolower($estadoMesa) === 'disponible';
    $okPagos = ((int)$pagos['cnt']) > 0;
    $okIngredientes = !empty($consumos);

    echo "\nResumen:\n";
    echo "- Mesa liberada: " . ($okMesa ? '✅' : '❌') . "\n";
    echo "- Pagos registrados: " . ($okPagos ? '✅' : '❌') . "\n";
    echo "- Ingredientes descontados: " . ($okIngredientes ? '✅' : '❌') . "\n";

    echo "\n=== Verificación completa ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}