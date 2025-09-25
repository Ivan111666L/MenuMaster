<?php
// Set environment variables for database connection
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'menu_master';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_CHARSET'] = 'utf8mb4';

require_once 'App/config/conexionDb.php';
require_once 'App/Models/ProductoModel.php';

use App\Config\ConexionDb;
use App\Models\ProductoModel;

try {
    $db = ConexionDb::getConnection();
    $productoModel = new ProductoModel($db);
    
    echo "=== DEBUGGING PRODUCT CREATION ===\n";
    
    // Test data
    $productData = [
        'nombre' => 'Test Product Debug',
        'descripcion' => 'A test product for debugging',
        'precio' => 15.99,
        'categoria_id' => 2, // Platos Fuertes
        'tiempo_preparacion_min' => 20,
        'estado_id' => 1, // disponible
        'destacado' => 0
    ];
    
    echo "Product data to create:\n";
    print_r($productData);
    
    // Check if product with same name exists
    echo "\n=== CHECKING FOR EXISTING PRODUCT ===\n";
    $stmt = $db->prepare("SELECT * FROM productos WHERE nombre = ?");
    $stmt->execute([$productData['nombre']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "❌ Product with name '{$productData['nombre']}' already exists:\n";
        print_r($existing);
        
        // Delete it first
        echo "\nDeleting existing product...\n";
        $deleteStmt = $db->prepare("DELETE FROM productos WHERE nombre = ?");
        $deleteStmt->execute([$productData['nombre']]);
        echo "✅ Existing product deleted\n";
    } else {
        echo "✅ No existing product found with this name\n";
    }
    
    // Try to create the product
    echo "\n=== ATTEMPTING PRODUCT CREATION ===\n";
    
    try {
        $result = $productoModel->create($productData);
        
        if ($result) {
            echo "✅ Product created successfully!\n";
            echo "Product ID: " . $result . "\n";
            
            // Verify the product was created
            $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$result]);
            $createdProduct = $stmt->fetch();
            
            echo "\nCreated product details:\n";
            print_r($createdProduct);
        } else {
            echo "❌ Product creation failed - returned false\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Exception during product creation:\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
}