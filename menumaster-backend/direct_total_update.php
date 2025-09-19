<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

$db = ConexionDb::getConnection();

// Direct SQL updates
$db->exec("UPDATE pedidos SET total = 41.00 WHERE id = 10");
$db->exec("UPDATE pedidos SET total = 20.50 WHERE id = 11");
$db->exec("UPDATE pedidos SET total = 23.50 WHERE id = 12");

echo "Totals updated successfully!\n";

// Verify
$stmt = $db->query("SELECT id, total FROM pedidos WHERE id IN (10, 11, 12) ORDER BY id");
while ($row = $stmt->fetch()) {
    echo "Order #" . $row['id'] . ": $" . $row['total'] . "\n";
}
?>