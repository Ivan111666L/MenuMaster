<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;
$db = ConexionDb::getConnection();

echo "=== ALL RECENT ORDERS ===\n";
$stmt = $db->query('SELECT p.id, p.total, ep.nombre as estado, p.fecha_creacion FROM pedidos p LEFT JOIN estados_pedido ep ON p.estado_id = ep.id ORDER BY p.id DESC LIMIT 10');
while ($row = $stmt->fetch()) {
    echo $row['id'] . ' - $' . $row['total'] . ' - ' . $row['estado'] . ' - ' . $row['fecha_creacion'] . "\n";
}
?>