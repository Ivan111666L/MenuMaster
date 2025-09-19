<?php
// Script para calcular y actualizar los totales correctamente
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

echo "=== FIXING ALL ORDER TOTALS ===\n\n";

try {
    $db = ConexionDb::getConnection();
    $db->beginTransaction();
    
    // Update all orders using a direct SQL query
    $stmt = $db->query("
        UPDATE pedidos p 
        SET total = (
            SELECT COALESCE(SUM(dp.subtotal), 0)
            FROM detalles_pedido dp 
            WHERE dp.pedido_id = p.id
        )
    ");
    
    $affectedRows = $stmt->rowCount();
    echo "Updated $affectedRows orders\n\n";
    
    $db->commit();
    
    // Verify the results
    echo "Verification - Recent orders:\n";
    $stmt = $db->query("
        SELECT 
            p.id,
            p.total,
            m.numero as mesa_numero,
            ep.nombre as estado,
            p.fecha_creacion
        FROM pedidos p
        LEFT JOIN mesas m ON p.mesa_id = m.id
        LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
        ORDER BY p.id DESC
        LIMIT 5
    ");
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pedidos as $pedido) {
        echo "Order #" . $pedido['id'] . " - Mesa " . $pedido['mesa_numero'] . " - " . $pedido['estado'] . " - Total: \$" . number_format($pedido['total'], 2) . "\n";
    }
    
    echo "\n=== TOTALS SUCCESSFULLY UPDATED ===\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>