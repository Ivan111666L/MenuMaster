<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

echo "=== MANUAL TOTAL UPDATE ===\n\n";

try {
    $db = ConexionDb::getConnection();
    
    // Manual update for each order
    $orders = [10, 11, 12];
    
    foreach ($orders as $orderId) {
        // Calculate total
        $stmt = $db->prepare("SELECT SUM(subtotal) as total FROM detalles_pedido WHERE pedido_id = ?");
        $stmt->execute([$orderId]);
        $total = $stmt->fetchColumn();
        
        // Update total
        $stmt = $db->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
        $success = $stmt->execute([$total, $orderId]);
        
        echo "Order #$orderId: Total $total - " . ($success ? "✓ Updated" : "✗ Failed") . "\n";
    }
    
    echo "\nVerification:\n";
    $stmt = $db->query("
        SELECT id, total, mesa_id 
        FROM pedidos 
        WHERE id IN (10, 11, 12) 
        ORDER BY id DESC
    ");
    
    while ($row = $stmt->fetch()) {
        echo "Order #" . $row['id'] . ": $" . $row['total'] . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>