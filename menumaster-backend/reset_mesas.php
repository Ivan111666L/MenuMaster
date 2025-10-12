<?php
// Script para resetear todas las mesas al estado 'DISPONIBLE'

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/App/config/Constantes.php';
require_once __DIR__ . '/App/config/conexionDb.php';

use App\Config\ConexionDb;

try {
    $db = ConexionDb::getConnection();
    $estadoDisponibleId = \App\EstadosMesa::DISPONIBLE;

    $stmt = $db->prepare('UPDATE mesas SET estado_id = :estado_id');
    $stmt->bindValue(':estado_id', $estadoDisponibleId, PDO::PARAM_INT);
    $stmt->execute();
    
    $count = $stmt->rowCount();
    echo "✅ Mesas reseteadas a DISPONIBLE (id={$estadoDisponibleId}). Filas afectadas: {$count}\n";

    // Mostrar resumen rápido
    $summary = $db->query("SELECT em.nombre AS estado, COUNT(*) AS total
                            FROM mesas m
                            LEFT JOIN estados_mesa em ON m.estado_id = em.id
                            GROUP BY em.nombre")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nResumen de estados de mesas:\n";
    foreach ($summary as $row) {
        echo "- {$row['estado']}: {$row['total']}\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}