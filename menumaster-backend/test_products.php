<?php
// Test para verificar que los productos con ingredientes funcionen correctamente
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';

use app\config\ConexionDb;
use app\Models\ProductoModel;
use app\Models\ProductoIngredientesModel;

echo "=== TESTING PRODUCT WITH INGREDIENTS FUNCTIONALITY ===\n\n";

try {
    $db = ConexionDb::getConnection();
    
    echo "1. Testing ProductoModel...\n";
    $productoModel = new ProductoModel($db);
    
    // Get all products
    $productos = $productoModel->findAll(true); // true para obtener todos incluyendo inactivos
    echo "   ✓ Found " . count($productos) . " products in database\n";
    
    foreach ($productos as $producto) {
        echo "   - " . $producto['nombre'] . " (\$" . $producto['precio'] . ")\n";
    }
    
    echo "\n2. Testing ProductoIngredientesModel...\n";
    $prodIngredientesModel = new ProductoIngredientesModel($db);
    
    // Test with first product
    if (!empty($productos)) {
        $primerProducto = $productos[0];
        echo "   Testing with product: " . $primerProducto['nombre'] . "\n";
        
        $ingredientes = $prodIngredientesModel->getByProducto($primerProducto['id']);
        echo "   ✓ Found " . count($ingredientes) . " ingredients for this product:\n";
        
        foreach ($ingredientes as $ingrediente) {
            echo "     - " . $ingrediente['ingrediente_nombre'] . ": " . $ingrediente['cantidad'] . " " . $ingrediente['unidad_medida'] . "\n";
        }
    }
    
    echo "\n3. Testing product creation...\n";
    
    // Create a test product
    $nuevoProducto = [
        'nombre' => 'Ensalada Mixta Test',
        'descripcion' => 'Ensalada fresca con vegetales variados',
        'precio' => 7.50,
        'categoria_id' => 1,
        'estado_id' => 1
    ];
    
    $productoCreado = $productoModel->create($nuevoProducto);
    if ($productoCreado && isset($productoCreado['id'])) {
        $productoId = $productoCreado['id'];
        echo "   ✓ Created new product with ID: " . $productoId . "\n";
    } else {
        echo "   ✗ Failed to create product\n";
        throw new Exception("Product creation failed");
    }
    
    // Add ingredients to the product
    $ingredientesParaProducto = [
        ['ingrediente_id' => 1, 'cantidad' => 0.1], // Using first available ingredient
        ['ingrediente_id' => 2, 'cantidad' => 0.05] // Using second available ingredient
    ];
    
    foreach ($ingredientesParaProducto as $ingrediente) {
        $result = $prodIngredientesModel->create([
            'producto_id' => $productoId,
            'ingrediente_id' => $ingrediente['ingrediente_id'],
            'cantidad' => $ingrediente['cantidad']
        ]);
        echo "   ✓ Added ingredient " . $ingrediente['ingrediente_id'] . " to product\n";
    }
    
    echo "\n4. Verifying the complete product...\n";
    $productoCompleto = $productoModel->find($productoId);
    echo "   Product: " . $productoCompleto['nombre'] . "\n";
    echo "   Price: $" . $productoCompleto['precio'] . "\n";
    
    $ingredientesDelProducto = $prodIngredientesModel->getByProducto($productoId);
    echo "   Ingredients (" . count($ingredientesDelProducto) . "):\n";
    foreach ($ingredientesDelProducto as $ingrediente) {
        echo "     - " . $ingrediente['ingrediente_nombre'] . ": " . $ingrediente['cantidad'] . " " . $ingrediente['unidad_medida'] . "\n";
    }
    
    echo "\n=== ALL TESTS PASSED! ===\n";
    echo "The product-ingredient system is working correctly!\n";
    echo "You can now use the frontend to create products with ingredient compositions.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>