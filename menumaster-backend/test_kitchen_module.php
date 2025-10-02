<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';
require_once 'App/models/MenuDelDiaModel.php';
require_once 'App/models/PedidoModel.php';

use App\Config\ConexionDb;
use App\Models\MenuDelDiaModel;
use App\Models\PedidoModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Database connection
    $db = ConexionDb::getConnection();
    echo "✅ Database connected successfully\n";

    // Get admin user for JWT token
    $stmt = $db->prepare("SELECT u.*, r.nombre as role_name, r.id as role_id FROM usuarios u 
                         LEFT JOIN roles r ON u.rol_id = r.id 
                         WHERE u.email = 'admin@menumaster.com' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Admin user not found");
    }

    echo "✅ Using admin user: " . $user['nombre'] . " (ID: " . $user['id'] . ")\n";

    // Generate JWT token
    $secretKey = "your-secret-key-here";
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role_name'],
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    echo "✅ JWT token generated\n";

    // Test Kitchen Controllers
    echo "\n=== Testing Kitchen Module ===\n";
    
    $menuDelDiaModel = new MenuDelDiaModel($db);
    $pedidoModel = new PedidoModel($db);

    // Test 1: Get menú del día (modelo directo)
    echo "1. Testing menu del dia (model getForToday)...\n";
    try {
        $items = $menuDelDiaModel->getForToday();
        if ($items === false) {
            echo "❌ Failed to get menu del dia\n";
        } else {
            echo "✅ Menu del dia retrieved successfully\n";
            echo "   - Found " . (is_array($items) ? count($items) : 0) . " menu items\n";
            foreach (array_slice(is_array($items) ? $items : [], 0, 3) as $item) {
                $nombre = $item['producto_nombre'] ?? ($item['nombre'] ?? 'Producto');
                $disponible = $item['cantidad_disponible'] ?? ($item['stock_actual'] ?? 'N/A');
                echo "   - {$nombre}: {$disponible} available\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error testing menu del dia (model): " . $e->getMessage() . "\n";
    }

    // Test 2: Get all orders (modelo directo)
    echo "\n2. Testing orders list (model findAll)...\n";
    try {
        $orders = $pedidoModel->findAll();
        if ($orders === false) {
            echo "❌ Failed to get orders\n";
        } else {
            echo "✅ Orders retrieved successfully\n";
            echo "   - Found " . (is_array($orders) ? count($orders) : 0) . " orders\n";
            foreach (array_slice(is_array($orders) ? $orders : [], 0, 3) as $order) {
                $mesa = $order['mesa_numero'] ?? ($order['mesa_id'] ?? 'N/A');
                $estado = $order['estado'] ?? ($order['estado_nombre'] ?? 'N/A');
                echo "   - Order #{$order['id']}: Mesa {$mesa} - {$estado}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error testing orders list (model): " . $e->getMessage() . "\n";
    }

    // Test 3: Create menú del día item
    echo "\n3. Testing menu del dia creation...\n";
    try {
        // First get a product ID
        $stmt = $db->prepare("SELECT id FROM productos WHERE estado_id = 1 LIMIT 1");
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $ok = $menuDelDiaModel->add((int)$product['id']);
            if ($ok) {
                echo "✅ Menu del dia item created successfully\n";
            } else {
                echo "❌ Failed to create menu del dia item\n";
            }
        } else {
            echo "❌ No products found to create menu del dia item\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing menu del dia creation: " . $e->getMessage() . "\n";
    }

    // Test 4: Update order status (simulate kitchen workflow)
    echo "\n4. Testing order status update...\n";
    try {
        // Get an existing order not marked as 'entregado'
        $stmt = $db->prepare("SELECT p.id 
                               FROM pedidos p 
                               LEFT JOIN estados_pedido ep ON p.estado_id = ep.id 
                               WHERE ep.nombre != 'entregado' 
                               LIMIT 1");
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            // Ensure target estado exists in estados_pedido
            $targetEstado = 'completado';
            $checkEstadoStmt = $db->prepare("SELECT id FROM estados_pedido WHERE nombre = :nombre");
            $checkEstadoStmt->bindParam(':nombre', $targetEstado);
            $checkEstadoStmt->execute();
            $estado = $checkEstadoStmt->fetch(PDO::FETCH_ASSOC);

            if (!$estado) {
                $insertEstadoStmt = $db->prepare("INSERT INTO estados_pedido (nombre) VALUES (:nombre)");
                $insertEstadoStmt->bindParam(':nombre', $targetEstado);
                $insertEstadoStmt->execute();
            }

            $ok = $pedidoModel->actualizarEstadoPedido((int)$order['id'], $targetEstado);
            if ($ok) {
                echo "✅ Order status updated successfully\n";
                echo "   - Order ID: {$order['id']} set to '{$targetEstado}'\n";
            } else {
                echo "❌ Failed to update order status\n";
            }
        } else {
            echo "❌ No orders found to update status\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing order status update: " . $e->getMessage() . "\n";
    }

    // Test 5: Get orders by status (kitchen queue)
    echo "\n5. Testing orders by status...\n";
    try {
        $orders = $pedidoModel->findAll('pendiente');
        if ($orders === false) {
            echo "❌ Failed to get orders by status\n";
        } else {
            echo "✅ Orders by status retrieved successfully\n";
            echo "   - Found " . (is_array($orders) ? count($orders) : 0) . " pending orders\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing orders by status: " . $e->getMessage() . "\n";
    }

    echo "\n=== Kitchen Module Testing Complete ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}