<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;
$db = ConexionDb::getConnection();

echo "=== PEDIDOS TABLE STRUCTURE ===\n";
$stmt = $db->query('DESCRIBE pedidos');
while ($row = $stmt->fetch()) {
    if ($row['Field'] == 'total') {
        print_r($row);
    }
}

echo "\n=== TESTING DIRECT UPDATE ===\n";
$stmt = $db->prepare('UPDATE pedidos SET total = 19.75 WHERE id = 3');
$stmt->execute();

$stmt = $db->query('SELECT id, total FROM pedidos WHERE id = 3');
$row = $stmt->fetch();
echo "Order 3 total after direct update: $" . $row['total'] . "\n";
?>