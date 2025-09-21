<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/config/conexionDb.php';

use app\config\ConexionDb;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = ConexionDb::getConnection();

echo "\n=== ESTADOS MESA ===\n";
$stmt = $db->prepare('SELECT * FROM estados_mesa ORDER BY id');
$stmt->execute();
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($estados as $estado) {
    echo 'ID: ' . $estado['id'] . ' - Nombre: ' . $estado['nombre'] . PHP_EOL;
}

echo "\n=== ACTUALIZANDO MESAS A DISPONIBLE ===\n";
$stmt = $db->prepare('UPDATE mesas SET estado_id = 1 WHERE id IN (1,2,3,4)');
$result = $stmt->execute();
echo $result ? "Mesas actualizadas correctamente" : "Error al actualizar mesas";

echo "\n=== MESAS CON ESTADOS MESA ===\n";
$stmt = $db->prepare('SELECT m.id, m.numero, em.nombre as estado_mesa FROM mesas m LEFT JOIN estados_mesa em ON m.estado_id = em.id ORDER BY m.numero');
$stmt->execute();
$mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($mesas as $mesa) {
    echo 'Mesa ' . $mesa['numero'] . ' - Estado Mesa: ' . ($mesa['estado_mesa'] ?? 'NULL') . PHP_EOL;
}
?>