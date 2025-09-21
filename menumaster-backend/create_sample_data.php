<?php
// Script to create sample data for testing dashboard
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/App/config/conexionDb.php';

use app\config\ConexionDb;

echo "=== CREATING SAMPLE DATA FOR DASHBOARD TESTING ===\n\n";

try {
    $db = ConexionDb::getConnection();
    $db->beginTransaction();
    
    // 1. First, let's add some sample ingredients with stock
    echo "1. Adding sample ingredients...\n";
    $stmt = $db->prepare("
        INSERT INTO ingredientes (nombre, descripcion, unidad_medida, stock_actual, stock_minimo, precio_compra, estado_id) 
        VALUES 
        ('Tomate', 'Tomate fresco', 'kg', 50.00, 10.00, 2.50, 1),
        ('Queso', 'Queso mozzarella', 'kg', 5.00, 8.00, 12.00, 1),
        ('Harina', 'Harina para pizza', 'kg', 100.00, 20.00, 1.80, 1),
        ('Carne', 'Carne de res', 'kg', 3.00, 10.00, 15.00, 1),
        ('Pollo', 'Pechuga de pollo', 'kg', 25.00, 5.00, 8.50, 1)
    ");
    $stmt->execute();
    echo "   ✓ Ingredients added\n";
    
    // 2. Add some sample products
    echo "2. Adding sample products...\n";
    $stmt = $db->prepare("
        INSERT INTO productos (nombre, descripcion, precio, categoria_id, estado_id) 
        VALUES 
        ('Pizza Margherita', 'Pizza con tomate y queso', 12.50, 1, 1),
        ('Hamburguesa Clásica', 'Hamburguesa con carne y queso', 8.75, 1, 1),
        ('Pollo a la Plancha', 'Pechuga de pollo grillada', 10.00, 1, 1),
        ('Ensalada César', 'Ensalada fresca con pollo', 7.25, 1, 1)
    ");
    $stmt->execute();
    echo "   ✓ Products added\n";
    
    // 3. Set one table as occupied
    echo "3. Setting table 2 as occupied...\n";
    $stmt = $db->prepare("UPDATE mesas SET estado_id = 2 WHERE id = 2");
    $stmt->execute();
    echo "   ✓ Table 2 is now occupied\n";
    
    // 4. Create some sample orders for today
    echo "4. Creating sample orders for today...\n";
    
    // Order 1 - pending
    $stmt = $db->prepare("
        INSERT INTO pedidos (mesa_id, usuario_id, estado_id, total, fecha_creacion) 
        VALUES (2, 1, 1, 21.25, NOW())
    ");
    $stmt->execute();
    $orderId1 = $db->lastInsertId();
    
    // Add items to order 1
    $stmt = $db->prepare("
        INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
        VALUES 
        (?, 1, 1, 12.50, 12.50),
        (?, 4, 1, 7.25, 7.25)
    ");
    $stmt->execute([$orderId1, $orderId1]);
    
    // Order 2 - in preparation
    $stmt = $db->prepare("
        INSERT INTO pedidos (mesa_id, usuario_id, estado_id, total, fecha_creacion) 
        VALUES (1, 1, 2, 18.75, NOW())
    ");
    $stmt->execute();
    $orderId2 = $db->lastInsertId();
    
    // Add items to order 2
    $stmt = $db->prepare("
        INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
        VALUES 
        (?, 2, 2, 8.75, 17.50),
        (?, 3, 1, 10.00, 10.00)
    ");
    $stmt->execute([$orderId2, $orderId2]);
    
    // Order 3 - paid (completed today)
    $stmt = $db->prepare("
        INSERT INTO pedidos (mesa_id, usuario_id, estado_id, total, fecha_creacion) 
        VALUES (3, 1, 4, 15.25, NOW())
    ");
    $stmt->execute();
    $orderId3 = $db->lastInsertId();
    
    // Add items to order 3
    $stmt = $db->prepare("
        INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
        VALUES 
        (?, 1, 1, 12.50, 12.50),
        (?, 4, 1, 7.25, 7.25)
    ");
    $stmt->execute([$orderId3, $orderId3]);
    
    echo "   ✓ Created 3 sample orders\n";
    
    // 5. Create some orders from previous days for weekly sales
    echo "5. Creating historical orders for weekly sales...\n";
    
    for ($i = 1; $i <= 6; $i++) {
        $date = date('Y-m-d H:i:s', strtotime("-{$i} day"));
        $total = rand(50, 200) + (rand(0, 99) / 100); // Random total between 50-200
        
        $stmt = $db->prepare("
            INSERT INTO pedidos (mesa_id, usuario_id, estado_id, total, fecha_creacion) 
            VALUES (1, 1, 4, ?, ?)
        ");
        $stmt->execute([$total, $date]);
        
        $orderId = $db->lastInsertId();
        
        // Add some items to historical orders
        $stmt = $db->prepare("
            INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
            VALUES (?, 1, 2, 12.50, 25.00)
        ");
        $stmt->execute([$orderId]);
    }
    
    echo "   ✓ Created 6 historical orders\n";
    
    $db->commit();
    
    echo "\n=== SAMPLE DATA CREATION COMPLETED ===\n";
    echo "Summary of created data:\n";
    echo "- 5 ingredients (2 with low stock)\n";
    echo "- 4 products\n";
    echo "- 1 occupied table (out of 4)\n";
    echo "- 2 active orders (1 pending, 1 in preparation)\n";
    echo "- 1 completed order today\n";
    echo "- 6 historical orders for weekly sales\n";
    echo "\nYou can now test the dashboard with real data!\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>