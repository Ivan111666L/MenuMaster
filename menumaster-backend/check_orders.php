<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

$db = ConexionDb::getConnection();

echo "=== ORDER STATES ===\n";
$stmt = $db->query('SELECT * FROM estados_pedido');
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . " - Name: " . $row['nombre'] . "\n";
}

echo "\n=== CURRENT ORDERS ===\n";
$stmt = $db->query('SELECT p.id, p.total, ep.nombre as estado FROM pedidos p LEFT JOIN estados_pedido ep ON p.estado_id = ep.id WHERE DATE(p.fecha_creacion) = CURDATE()');
while ($row = $stmt->fetch()) {
    echo "Order " . $row['id'] . ": $" . $row['total'] . " - Status: " . $row['estado'] . "\n";
}
?>