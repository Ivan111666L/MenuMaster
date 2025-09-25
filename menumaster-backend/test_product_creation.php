<?php
// Test product creation endpoint
require_once __DIR__ . '/vendor/autoload.php';

echo "=== TESTING PRODUCT CREATION ===\n\n";

// First, login to get a token
$loginUrl = 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/login';
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Login failed with HTTP code: $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

$loginResult = json_decode($response, true);
if (!isset($loginResult['token'])) {
    echo "❌ No token received from login\n";
    exit(1);
}

$token = $loginResult['token'];
echo "✅ Login successful, token obtained\n\n";

// Now test product creation
echo "Testing product creation...\n";

$productUrl = 'http://localhost/MenuMaster/menumaster-backend/public/api/productos';
$productData = [
    'nombre' => 'Test Product',
    'descripcion' => 'A test product for debugging',
    'precio' => 15.99,
    'categoria_id' => 1,
    'tiempo_preparacion_min' => 10,
    'destacado' => false,
    'ingredientes' => [
        [
            'ingrediente_id' => 1,
            'cantidad' => 2.5
        ]
    ]
];

echo "Product data: " . json_encode($productData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $productUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($error) {
    echo "cURL Error: $error\n";
}

if ($httpCode === 201) {
    $result = json_decode($response, true);
    if ($result && $result['success']) {
        echo "✅ Product creation successful!\n";
        echo "Product ID: " . $result['data']['id'] . "\n";
    } else {
        echo "❌ Product creation failed - invalid response format\n";
    }
} else {
    echo "❌ Product creation failed with HTTP code: $httpCode\n";
}

echo "\n🏁 Product creation test complete!\n";
?>