<?php
// Test script to debug ingredient creation endpoint
require_once 'vendor/autoload.php';

// Test data for ingredient creation
$testData = [
    'nombre' => 'Tomate Test',
    'descripcion' => 'Tomate fresco para ensaladas',
    'unidad_medida' => 'kilogramos',
    'stock_actual' => 10.5,
    'stock_minimo' => 2.0,
    'precio_compra' => 3.50
];

echo "=== TESTING INGREDIENT CREATION ENDPOINT ===\n";
echo "Test data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Test 1: Direct API call
echo "1. Testing direct API call to /api/ingredientes\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/MenuMaster/menumaster-backend/public/api/ingredientes');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

// Test 2: Check if response is valid JSON
$decodedResponse = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "2. Response is valid JSON:\n";
    echo json_encode($decodedResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    // Check response structure
    echo "3. Response structure analysis:\n";
    echo "- Has 'success' key: " . (isset($decodedResponse['success']) ? 'YES' : 'NO') . "\n";
    echo "- Has 'data' key: " . (isset($decodedResponse['data']) ? 'YES' : 'NO') . "\n";
    echo "- Has 'error' key: " . (isset($decodedResponse['error']) ? 'YES' : 'NO') . "\n";
    
    if (isset($decodedResponse['data']) && is_array($decodedResponse['data'])) {
        echo "- Data has 'nombre' key: " . (isset($decodedResponse['data']['nombre']) ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "2. Response is NOT valid JSON. JSON Error: " . json_last_error_msg() . "\n";
    echo "Raw response: $response\n\n";
}

// Test 3: Check if it's HTML response (error page)
if (strpos($response, '<html>') !== false || strpos($response, '<!DOCTYPE') !== false) {
    echo "3. Response appears to be HTML (error page)\n";
    echo "First 200 characters: " . substr($response, 0, 200) . "...\n\n";
}

echo "=== END OF TEST ===\n";
?>