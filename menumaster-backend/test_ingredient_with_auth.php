<?php
// Test script to debug ingredient creation endpoint with authentication
require_once 'vendor/autoload.php';

echo "=== TESTING INGREDIENT CREATION WITH AUTH ===\n";

// Step 1: Login to get a token
echo "1. Logging in to get authentication token...\n";
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Status: $loginHttpCode\n";
echo "Login Response: $loginResponse\n\n";

$loginData = json_decode($loginResponse, true);
if (!isset($loginData['token'])) {
    echo "ERROR: Could not get authentication token\n";
    exit(1);
}

$token = $loginData['token'];
echo "Got token: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Test ingredient creation with token
echo "2. Testing ingredient creation with authentication...\n";
$testData = [
    'nombre' => 'Tomate Test Auth',
    'descripcion' => 'Tomate fresco para ensaladas con auth',
    'unidad_medida' => 'kilogramos',
    'stock_actual' => 10.5,
    'stock_minimo' => 2.0,
    'precio_compra' => 3.50
];

echo "Test data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/MenuMaster/menumaster-backend/public/api/ingredientes');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

// Analyze response
$decodedResponse = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "3. Response analysis:\n";
    echo json_encode($decodedResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "Response structure:\n";
    echo "- Has 'success' key: " . (isset($decodedResponse['success']) ? 'YES' : 'NO') . "\n";
    echo "- Has 'data' key: " . (isset($decodedResponse['data']) ? 'YES' : 'NO') . "\n";
    echo "- Has 'error' key: " . (isset($decodedResponse['error']) ? 'YES' : 'NO') . "\n";
    
    if (isset($decodedResponse['data']) && is_array($decodedResponse['data'])) {
        echo "- Data has 'nombre' key: " . (isset($decodedResponse['data']['nombre']) ? 'YES' : 'NO') . "\n";
        if (isset($decodedResponse['data']['nombre'])) {
            echo "- Ingredient name: " . $decodedResponse['data']['nombre'] . "\n";
        }
    }
    
    if ($httpCode === 201 && isset($decodedResponse['success']) && $decodedResponse['success']) {
        echo "\n✅ SUCCESS: Ingredient created successfully!\n";
    } else {
        echo "\n❌ FAILED: Ingredient creation failed\n";
        if (isset($decodedResponse['error'])) {
            echo "Error: " . $decodedResponse['error'] . "\n";
        }
    }
} else {
    echo "3. Response is NOT valid JSON. JSON Error: " . json_last_error_msg() . "\n";
    echo "Raw response: $response\n\n";
}

echo "=== END OF TEST ===\n";
?>