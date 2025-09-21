<?php
// Sistema de impresión de pedidos que funciona con los datos existentes
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../App/config/conexionDb.php';
use app\config\ConexionDb;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = ConexionDb::getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $pedidoId = $_GET['id'] ?? null;
        
        if (!$pedidoId) {
            throw new Exception('Se requiere el ID del pedido');
        }
        
        // Get complete order information
        $stmt = $db->prepare("
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
                p.fecha_creacion,
                p.fecha_actualizacion
            FROM pedidos p
            LEFT JOIN mesas m ON p.mesa_id = m.id
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pedidoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception('Pedido no encontrado');
        }
        
        // Get order items with ingredients
        $stmt = $db->prepare("
            SELECT 
                dp.cantidad,
                dp.precio_unitario,
                dp.subtotal,
                pr.nombre as producto_nombre,
                pr.descripcion as producto_descripcion,
                pr.id as producto_id
            FROM detalles_pedido dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
            ORDER BY dp.id
        ");
        $stmt->execute([$pedidoId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add ingredients to each item
        foreach ($items as &$item) {
            $stmt = $db->prepare("
                SELECT 
                    pi.cantidad,
                    i.nombre as ingrediente_nombre,
                    i.unidad_medida
                FROM productos_ingredientes pi
                JOIN ingredientes i ON pi.ingrediente_id = i.id
                WHERE pi.producto_id = ?
            ");
            $stmt->execute([$item['producto_id']]);
            $item['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $pedido['items'] = $items;
        
        // Calculate total from items
        $totalCalculado = array_sum(array_column($items, 'subtotal'));
        $pedido['total_calculado'] = $totalCalculado;
        
        echo json_encode([
            'success' => true,
            'data' => $pedido
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Generate printable order
        $input = json_decode(file_get_contents('php://input'), true);
        $pedidoId = $input['id'] ?? null;
        
        if (!$pedidoId) {
            throw new Exception('Se requiere el ID del pedido');
        }
        
        // Get order data (reuse the GET logic)
        $stmt = $db->prepare("
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
                p.fecha_creacion
            FROM pedidos p
            LEFT JOIN mesas m ON p.mesa_id = m.id
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pedidoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("
            SELECT 
                dp.cantidad,
                dp.precio_unitario,
                dp.subtotal,
                pr.nombre as producto_nombre,
                pr.descripcion as producto_descripcion,
                pr.id as producto_id
            FROM detalles_pedido dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
        ");
        $stmt->execute([$pedidoId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate printable ticket
        $ticket = generateTicket($pedido, $items);
        
        echo json_encode([
            'success' => true,
            'ticket' => $ticket,
            'html' => generateTicketHTML($pedido, $items)
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function generateTicket($pedido, $items) {
    $ticket = "================================\n";
    $ticket .= "         PEDIDO COMPLETO\n";
    $ticket .= "================================\n";
    $ticket .= "Pedido #: " . $pedido['id'] . "\n";
    $ticket .= "Mesa: " . $pedido['mesa_numero'] . " (" . $pedido['mesa_ubicacion'] . ")\n";
    $ticket .= "Mesero: " . $pedido['usuario_nombre'] . "\n";
    $ticket .= "Estado: " . strtoupper($pedido['estado_nombre']) . "\n";
    $ticket .= "Fecha: " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "\n";
    $ticket .= "--------------------------------\n";
    
    $total = 0;
    foreach ($items as $item) {
        $ticket .= sprintf("%dx %-20s %8s\n", 
            $item['cantidad'], 
            substr($item['producto_nombre'], 0, 20),
            '$' . number_format($item['subtotal'], 2)
        );
        $total += $item['subtotal'];
    }
    
    $ticket .= "--------------------------------\n";
    $ticket .= sprintf("TOTAL: %24s\n", '$' . number_format($total, 2));
    
    if ($pedido['notas']) {
        $ticket .= "--------------------------------\n";
        $ticket .= "NOTAS:\n";
        $ticket .= wordwrap($pedido['notas'], 32) . "\n";
    }
    
    $ticket .= "================================\n";
    $ticket .= "    Gracias por su visita\n";
    $ticket .= "================================\n";
    
    return $ticket;
}

function generateTicketHTML($pedido, $items) {
    $html = "<div style='font-family: monospace; width: 300px; margin: 0 auto; border: 1px solid #ccc; padding: 20px;'>";
    $html .= "<h2 style='text-align: center; margin: 0;'>PEDIDO COMPLETO</h2>";
    $html .= "<hr>";
    $html .= "<p><strong>Pedido #:</strong> " . $pedido['id'] . "</p>";
    $html .= "<p><strong>Mesa:</strong> " . $pedido['mesa_numero'] . " (" . $pedido['mesa_ubicacion'] . ")</p>";
    $html .= "<p><strong>Mesero:</strong> " . $pedido['usuario_nombre'] . "</p>";
    $html .= "<p><strong>Estado:</strong> " . strtoupper($pedido['estado_nombre']) . "</p>";
    $html .= "<p><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "</p>";
    $html .= "<hr>";
    
    $total = 0;
    foreach ($items as $item) {
        $html .= "<div style='display: flex; justify-content: space-between;'>";
        $html .= "<span>" . $item['cantidad'] . "x " . $item['producto_nombre'] . "</span>";
        $html .= "<span>$" . number_format($item['subtotal'], 2) . "</span>";
        $html .= "</div>";
        $total += $item['subtotal'];
    }
    
    $html .= "<hr>";
    $html .= "<div style='display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2em;'>";
    $html .= "<span>TOTAL:</span>";
    $html .= "<span>$" . number_format($total, 2) . "</span>";
    $html .= "</div>";
    
    if ($pedido['notas']) {
        $html .= "<hr>";
        $html .= "<p><strong>NOTAS:</strong></p>";
        $html .= "<p>" . htmlspecialchars($pedido['notas']) . "</p>";
    }
    
    $html .= "<hr>";
    $html .= "<p style='text-align: center; font-style: italic;'>Gracias por su visita</p>";
    $html .= "</div>";
    
    return $html;
}
?>