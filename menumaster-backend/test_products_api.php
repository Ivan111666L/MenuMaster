<?php
// Test para verificar que el endpoint de productos funcione correctamente
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== TESTING PRODUCTS API ENDPOINT ===\n\n";

// Test 1: Obtener productos directamente del modelo
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;
use app\Models\ProductoModel;

try {
    $db = ConexionDb::getConnection();
    $productoModel = new ProductoModel($db);
    
    echo "1. Testing ProductoModel directly...\n";
    $productos = $productoModel->findAll(true);
    echo "   ✓ Found " . count($productos) . " products\n";
    
    // Display first few products
    for ($i = 0; $i < min(3, count($productos)); $i++) {
        $p = $productos[$i];
        echo "   - " . $p['nombre'] . " (\$" . $p['precio'] . ") - " . $p['categoria_nombre'] . "\n";
    }
    
    echo "\n2. Testing API endpoint simulation...\n";
    
    // Simulate an API request for products
    $url = 'http://localhost/MenuMaster/menumaster-backend/public/simple_productos.php';
    echo "   Testing URL: " . $url . "\n";
    
    // Use curl to test the endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP Status: " . $httpCode . "\n";
    
    if ($curlError) {
        echo "   ✗ CURL Error: " . $curlError . "\n";
    } else if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['data'])) {
            echo "   ✓ API returned " . count($responseData['data']) . " products\n";
        } else {
            echo "   ✗ API response format issue\n";
            echo "   Response: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "   ✗ API returned HTTP " . $httpCode . "\n";
        echo "   Response: " . substr($response, 0, 200) . "...\n";
    }
    
    echo "\n=== API TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>