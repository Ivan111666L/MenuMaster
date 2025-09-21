<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

$db = ConexionDb::getConnection();

echo "=== FIXING ORDER TOTALS ===\n";

// Update all order totals based on their detail items
$stmt = $db->query('
    UPDATE pedidos p 
    SET total = (
        SELECT COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) 
        FROM detalles_pedido dp 
        WHERE dp.pedido_id = p.id
    )
');

echo "✓ Updated order totals\n";

// Verify the updates
$stmt = $db->query('
    SELECT p.id, p.total, ep.nombre as estado
    FROM pedidos p 
    LEFT JOIN estados_pedido ep ON p.estado_id = ep.id 
    WHERE DATE(p.fecha_creacion) = CURDATE()
');
while ($row = $stmt->fetch()) {
    echo "Order " . $row['id'] . ": $" . $row['total'] . " - " . $row['estado'] . "\n";
}
?>