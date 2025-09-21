<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use app\config\ConexionDb;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = ConexionDb::getConnection();
echo "Estructura tabla ingredientes:\n";
$stmt = $db->query('DESCRIBE ingredientes');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['Field']} ({$row['Type']})\n";
}
?>