<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;
$db = ConexionDb::getConnection();

echo "=== ORDER 3 DETAILS ===\n";
$stmt = $db->query('SELECT * FROM detalles_pedido WHERE pedido_id = 3');
while ($row = $stmt->fetch()) {
    print_r($row);
}

echo "\n=== CALCULATED TOTAL FOR ORDER 3 ===\n";
$stmt = $db->query('SELECT SUM(cantidad * precio_unitario) as total FROM detalles_pedido WHERE pedido_id = 3');
$total = $stmt->fetchColumn();
echo "Total: $" . ($total ?: '0.00') . "\n";

echo "\n=== MANUAL UPDATE FOR ORDER 3 ===\n";
$stmt = $db->prepare('UPDATE pedidos SET total = ? WHERE id = 3');
$result = $stmt->execute([$total]);
echo "Update result: " . ($result ? 'success' : 'failed') . "\n";

echo "\n=== VERIFY UPDATE ===\n";
$stmt = $db->query('SELECT total FROM pedidos WHERE id = 3');
echo "New total: $" . $stmt->fetchColumn() . "\n";
?>