<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;
$db = ConexionDb::getConnection();

$stmt = $db->query('SHOW CREATE TABLE pedidos');
$row = $stmt->fetch();
echo $row['Create Table'];
?>