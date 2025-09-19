<?php
// Script para crear datos de muestra completos: ingredientes, productos, y sus relaciones
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

echo "=== CREATING COMPREHENSIVE SAMPLE DATA ===\n\n";

try {
    $db = ConexionDb::getConnection();
    $db->beginTransaction();
    
    echo "1. Creating/getting ingredients...\n";
    
    $ingredientes = [
        ['nombre' => 'Arroz', 'descripcion' => 'Arroz blanco', 'unidad_medida' => 'kg', 'stock_actual' => 100.00, 'stock_minimo' => 20.00, 'precio_compra' => 2.80],
        ['nombre' => 'Frijoles', 'descripcion' => 'Frijoles negros', 'unidad_medida' => 'kg', 'stock_actual' => 80.00, 'stock_minimo' => 15.00, 'precio_compra' => 4.20],
        ['nombre' => 'Aceite', 'descripcion' => 'Aceite de cocina', 'unidad_medida' => 'litros', 'stock_actual' => 25.00, 'stock_minimo' => 5.00, 'precio_compra' => 3.50],
        ['nombre' => 'Sal', 'descripcion' => 'Sal de mesa', 'unidad_medida' => 'kg', 'stock_actual' => 50.00, 'stock_minimo' => 10.00, 'precio_compra' => 1.20],
        ['nombre' => 'Cebolla', 'descripcion' => 'Cebolla blanca', 'unidad_medida' => 'kg', 'stock_actual' => 30.00, 'stock_minimo' => 8.00, 'precio_compra' => 2.00],
        ['nombre' => 'Ajo', 'descripcion' => 'Ajo fresco', 'unidad_medida' => 'kg', 'stock_actual' => 15.00, 'stock_minimo' => 3.00, 'precio_compra' => 8.00],
        ['nombre' => 'Pasta', 'descripcion' => 'Pasta italiana', 'unidad_medida' => 'kg', 'stock_actual' => 40.00, 'stock_minimo' => 10.00, 'precio_compra' => 3.20],
        ['nombre' => 'Pan', 'descripcion' => 'Pan para hamburguesas', 'unidad_medida' => 'unidades', 'stock_actual' => 200.00, 'stock_minimo' => 50.00, 'precio_compra' => 0.80],
        ['nombre' => 'Lechuga', 'descripcion' => 'Lechuga fresca', 'unidad_medida' => 'kg', 'stock_actual' => 20.00, 'stock_minimo' => 5.00, 'precio_compra' => 2.50],
        ['nombre' => 'Papas', 'descripcion' => 'Papas para freír', 'unidad_medida' => 'kg', 'stock_actual' => 60.00, 'stock_minimo' => 15.00, 'precio_compra' => 1.80],
        ['nombre' => 'Tomate', 'descripcion' => 'Tomate fresco', 'unidad_medida' => 'kg', 'stock_actual' => 25.00, 'stock_minimo' => 5.00, 'precio_compra' => 3.00],
        ['nombre' => 'Carne', 'descripcion' => 'Carne de res', 'unidad_medida' => 'kg', 'stock_actual' => 30.00, 'stock_minimo' => 10.00, 'precio_compra' => 15.00],
        ['nombre' => 'Queso', 'descripcion' => 'Queso amarillo', 'unidad_medida' => 'kg', 'stock_actual' => 20.00, 'stock_minimo' => 5.00, 'precio_compra' => 8.50],
        ['nombre' => 'Pollo', 'descripcion' => 'Pechuga de pollo', 'unidad_medida' => 'kg', 'stock_actual' => 25.00, 'stock_minimo' => 8.00, 'precio_compra' => 12.00]
    ];
    
    $ingredienteIds = [];
    $ingredientesCreados = 0;
    foreach ($ingredientes as $ingrediente) {
        // Check if ingredient exists
        $stmt = $db->prepare("SELECT id FROM ingredientes WHERE nombre = ?");
        $stmt->execute([$ingrediente['nombre']]);
        $existingId = $stmt->fetchColumn();
        
        if ($existingId) {
            $ingredienteIds[$ingrediente['nombre']] = $existingId;
        } else {
            $stmt = $db->prepare("
                INSERT INTO ingredientes (nombre, descripcion, unidad_medida, stock_actual, stock_minimo, precio_compra, estado_id) 
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $ingrediente['nombre'],
                $ingrediente['descripcion'], 
                $ingrediente['unidad_medida'],
                $ingrediente['stock_actual'],
                $ingrediente['stock_minimo'],
                $ingrediente['precio_compra']
            ]);
            $ingredienteIds[$ingrediente['nombre']] = $db->lastInsertId();
            $ingredientesCreados++;
        }
    }
    echo "   ✓ Added " . $ingredientesCreados . " new ingredients (existing ones reused)\n";
    
    echo "2. Creating/getting categories...\n";
    $categorias = [
        ['nombre' => 'Platos Principales', 'descripcion' => 'Comidas principales del menú'],
        ['nombre' => 'Entradas', 'descripcion' => 'Aperitivos y entradas'],
        ['nombre' => 'Bebidas', 'descripcion' => 'Bebidas frías y calientes'],
        ['nombre' => 'Postres', 'descripcion' => 'Dulces y postres'],
        ['nombre' => 'Comida Rápida', 'descripcion' => 'Hamburguesas, hot dogs, etc.']
    ];
    
    $categoriaIds = [];
    $categoriasCreadas = 0;
    foreach ($categorias as $categoria) {
        // Check if category exists
        $stmt = $db->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $stmt->execute([$categoria['nombre']]);
        $existingId = $stmt->fetchColumn();
        
        if ($existingId) {
            $categoriaIds[$categoria['nombre']] = $existingId;
        } else {
            $stmt = $db->prepare("
                INSERT INTO categorias (nombre, descripcion, estado_id) 
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$categoria['nombre'], $categoria['descripcion']]);
            $categoriaIds[$categoria['nombre']] = $db->lastInsertId();
            $categoriasCreadas++;
        }
    }
    echo "   ✓ Added " . $categoriasCreadas . " new categories (existing ones reused)\n";
    
    echo "3. Creating products with ingredient relationships...\n";
    
    $productos = [
        [
            'nombre' => 'Arroz con Frijoles',
            'descripcion' => 'Plato tradicional con arroz y frijoles',
            'precio' => 8.50,
            'categoria' => 'Platos Principales',
            'ingredientes' => [
                ['Arroz' => 0.2],
                ['Frijoles' => 0.15],
                ['Aceite' => 0.02],
                ['Sal' => 0.01],
                ['Cebolla' => 0.05]
            ]
        ],
        [
            'nombre' => 'Pasta Italiana',
            'descripcion' => 'Pasta con salsa de tomate y ajo',
            'precio' => 12.00,
            'categoria' => 'Platos Principales',
            'ingredientes' => [
                ['Pasta' => 0.25],
                ['Tomate' => 0.1],
                ['Ajo' => 0.02],
                ['Aceite' => 0.03],
                ['Sal' => 0.01]
            ]
        ],
        [
            'nombre' => 'Hamburguesa Clásica',
            'descripcion' => 'Hamburguesa con carne, queso y vegetales',
            'precio' => 15.00,
            'categoria' => 'Comida Rápida',
            'ingredientes' => [
                ['Pan' => 1],
                ['Carne' => 0.15],
                ['Queso' => 0.05],
                ['Lechuga' => 0.03],
                ['Tomate' => 0.05],
                ['Cebolla' => 0.02]
            ]
        ],
        [
            'nombre' => 'Papas Fritas',
            'descripcion' => 'Papas cortadas y fritas',
            'precio' => 5.50,
            'categoria' => 'Entradas',
            'ingredientes' => [
                ['Papas' => 0.3],
                ['Aceite' => 0.1],
                ['Sal' => 0.005]
            ]
        ],
        [
            'nombre' => 'Pollo a la Plancha',
            'descripcion' => 'Pechuga de pollo grillada con especias',
            'precio' => 18.00,
            'categoria' => 'Platos Principales',
            'ingredientes' => [
                ['Pollo' => 0.25],
                ['Aceite' => 0.02],
                ['Sal' => 0.01],
                ['Ajo' => 0.01]
            ]
        ]
    ];
    
    foreach ($productos as $producto) {
        // Insertar producto
        $stmt = $db->prepare("
            INSERT INTO productos (nombre, descripcion, precio, categoria_id, estado_id) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $producto['nombre'],
            $producto['descripcion'],
            $producto['precio'],
            $categoriaIds[$producto['categoria']]
        ]);
        $productoId = $db->lastInsertId();
        
        // Insertar relaciones producto-ingrediente
        foreach ($producto['ingredientes'] as $ingredienteInfo) {
            foreach ($ingredienteInfo as $nombreIngrediente => $cantidad) {
                if (isset($ingredienteIds[$nombreIngrediente])) {
                    $stmt = $db->prepare("
                        INSERT INTO productos_ingredientes (producto_id, ingrediente_id, cantidad) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$productoId, $ingredienteIds[$nombreIngrediente], $cantidad]);
                }
            }
        }
    }
    echo "   ✓ Added " . count($productos) . " products with ingredient relationships\n";
    
    echo "4. Creating additional sample users...\n";
    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre, email, password, rol_id, estado_id) 
        VALUES 
        ('Mesero Demo', 'mesero@demo.com', ?, 2, 1),
        ('Chef Demo', 'chef@demo.com', ?, 3, 1)
    ");
    $passwordHash = password_hash('demo123', PASSWORD_DEFAULT);
    $stmt->execute([$passwordHash, $passwordHash]);
    echo "   ✓ Added demo users (mesero@demo.com, chef@demo.com - password: demo123)\n";
    
    echo "5. Creating sample orders to test the system...\n";
    
    // Get product IDs
    $stmt = $db->query("SELECT id, nombre FROM productos");
    $productosDisponibles = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Create 3 sample orders
    $pedidos = [
        [
            'mesa_id' => 1,
            'usuario_id' => 1,
            'estado_id' => 1, // pendiente
            'notas' => 'Sin cebolla, por favor',
            'items' => [
                ['producto' => 'Hamburguesa Clásica', 'cantidad' => 2],
                ['producto' => 'Papas Fritas', 'cantidad' => 2]
            ]
        ],
        [
            'mesa_id' => 3,
            'usuario_id' => 1,
            'estado_id' => 2, // en preparacion
            'notas' => 'Pasta al dente',
            'items' => [
                ['producto' => 'Pasta Italiana', 'cantidad' => 1],
                ['producto' => 'Arroz con Frijoles', 'cantidad' => 1]
            ]
        ],
        [
            'mesa_id' => 4,
            'usuario_id' => 1,
            'estado_id' => 4, // pagado
            'notas' => '',
            'items' => [
                ['producto' => 'Pollo a la Plancha', 'cantidad' => 1],
                ['producto' => 'Papas Fritas', 'cantidad' => 1]
            ]
        ]
    ];
    
    // Get product name to ID mapping
    $productNameToId = array_flip($productosDisponibles);
    
    foreach ($pedidos as $pedidoData) {
        // Insert order
        $stmt = $db->prepare("
            INSERT INTO pedidos (mesa_id, usuario_id, estado_id, notas, fecha_creacion) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $pedidoData['mesa_id'],
            $pedidoData['usuario_id'],
            $pedidoData['estado_id'],
            $pedidoData['notas']
        ]);
        $pedidoId = $db->lastInsertId();
        
        // Insert order items
        foreach ($pedidoData['items'] as $item) {
            if (isset($productNameToId[$item['producto']])) {
                $productoId = $productNameToId[$item['producto']];
                
                // Get product price
                $stmt = $db->prepare("SELECT precio FROM productos WHERE id = ?");
                $stmt->execute([$productoId]);
                $precio = $stmt->fetchColumn();
                
                $subtotal = $precio * $item['cantidad'];
                
                $stmt = $db->prepare("
                    INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$pedidoId, $productoId, $item['cantidad'], $precio, $subtotal]);
            }
        }
    }
    echo "   ✓ Added " . count($pedidos) . " sample orders with items\n";
    
    // Update table states
    echo "6. Updating table states...\n";
    $stmt = $db->prepare("UPDATE mesas SET estado_id = 2 WHERE id IN (1, 3)"); // occupied
    $stmt->execute();
    echo "   ✓ Set tables 1 and 3 as occupied\n";
    
    $db->commit();
    
    echo "\n=== SAMPLE DATA CREATION COMPLETED ===\n";
    echo "Summary of created data:\n";
    echo "- " . count($ingredientes) . " additional ingredients\n";
    echo "- " . count($categorias) . " product categories\n";
    echo "- " . count($productos) . " products with ingredient compositions\n";
    echo "- 2 demo users (mesero@demo.com, chef@demo.com)\n";
    echo "- " . count($pedidos) . " sample orders with items\n";
    echo "- Updated table states\n";
    echo "\nThe system is now ready for end-to-end testing!\n";
    echo "You can:\n";
    echo "1. Create ingredients from the frontend\n";
    echo "2. Create products with ingredient compositions\n";
    echo "3. Create orders selecting from available products\n";
    echo "4. View everything in the dashboard\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>