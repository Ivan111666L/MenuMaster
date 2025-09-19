<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;
$db = ConexionDb::getConnection();

echo "=== CHECKING ORDER TOTALS ===\n";
$stmt = $db->query('
    SELECT p.id, p.total, p.estado_id, ep.nombre as estado,
           (SELECT SUM(dp.cantidad * dp.precio_unitario) FROM detalles_pedido dp WHERE dp.pedido_id = p.id) as calculated_total
    FROM pedidos p 
    LEFT JOIN estados_pedido ep ON p.estado_id = ep.id 
    WHERE DATE(p.fecha_creacion) = CURDATE()
');
while ($row = $stmt->fetch()) {
    echo "Order " . $row['id'] . ": Stored=" . $row['total'] . " Calculated=" . $row['calculated_total'] . " Status=" . $row['estado'] . "\n";
}
?>