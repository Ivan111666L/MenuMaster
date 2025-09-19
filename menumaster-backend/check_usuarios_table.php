<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

$db = ConexionDb::getConnection();
$stmt = $db->query('DESCRIBE usuarios');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in usuarios table:\n";
foreach($columns as $col) {
    echo $col['Field'] . ' - ' . $col['Type'] . "\n";
}
?>