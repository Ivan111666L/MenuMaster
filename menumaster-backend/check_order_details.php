<?php
// Script para verificar en detalle los pedidos y sus totales
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

echo "=== DETAILED ORDERS CHECK ===\n\n";

try {
    $db = ConexionDb::getConnection();
    
    // Get detailed order information
    $stmt = $db->query("
        SELECT 
            p.id,
            p.mesa_id,
            m.numero as mesa_numero,
            m.ubicacion as mesa_ubicacion,
            p.usuario_id,
            u.nombre as usuario_nombre,
            p.estado_id,
            ep.nombre as estado_nombre,
            p.notas,
            p.total,
            p.fecha_creacion
        FROM pedidos p
        LEFT JOIN mesas m ON p.mesa_id = m.id
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
        WHERE p.id IN (10, 11, 12)
        ORDER BY p.id DESC
        LIMIT 3
    ");
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pedidos as $pedido) {
        echo "=== PEDIDO #" . $pedido['id'] . " ===\n";
        echo "Mesa: " . $pedido['mesa_numero'] . " (" . $pedido['mesa_ubicacion'] . ")\n";
        echo "Usuario: " . $pedido['usuario_nombre'] . "\n";
        echo "Estado: " . $pedido['estado_nombre'] . "\n";
        echo "Total en DB: $" . $pedido['total'] . "\n";
        echo "Notas: " . ($pedido['notas'] ?: 'Sin notas') . "\n";
        echo "Fecha: " . $pedido['fecha_creacion'] . "\n\n";
        
        // Get order details
        $stmt2 = $db->prepare("
            SELECT 
                dp.id as detalle_id,
                dp.cantidad,
                dp.precio_unitario,
                dp.subtotal,
                dp.producto_id,
                pr.nombre as producto_nombre,
                pr.descripcion as producto_descripcion,
                pr.precio as precio_actual_producto
            FROM detalles_pedido dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
            ORDER BY dp.id
        ");
        $stmt2->execute([$pedido['id']]);
        $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "DETALLES (" . count($detalles) . " items):\n";
        $totalCalculado = 0;
        
        foreach ($detalles as $detalle) {
            echo "  - ID: " . $detalle['detalle_id'] . "\n";
            echo "    Producto: " . $detalle['producto_nombre'] . " (ID: " . $detalle['producto_id'] . ")\n";
            echo "    Cantidad: " . $detalle['cantidad'] . "\n";
            echo "    Precio Unitario: $" . $detalle['precio_unitario'] . "\n";
            echo "    Subtotal: $" . $detalle['subtotal'] . "\n";
            echo "    Precio Actual en Productos: $" . $detalle['precio_actual_producto'] . "\n";
            $totalCalculado += $detalle['subtotal'];
            echo "  ---\n";
        }
        
        echo "TOTAL CALCULADO: $" . number_format($totalCalculado, 2) . "\n";
        echo "TOTAL EN DB: $" . $pedido['total'] . "\n";
        echo "DIFERENCIA: $" . number_format($totalCalculado - $pedido['total'], 2) . "\n";
        echo "========================\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>