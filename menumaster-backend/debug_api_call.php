<?php
// Set environment variables for database connection
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'menu_master';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_CHARSET'] = 'utf8mb4';

echo "=== DEBUGGING API CALL ===\n";

// First, let's login to get a token
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

$loginUrl = 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api/auth/login';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response (HTTP $loginHttpCode):\n";
echo $loginResponse . "\n\n";

$loginResult = json_decode($loginResponse, true);

if (!$loginResult || !isset($loginResult['token'])) {
    echo "❌ Failed to get authentication token\n";
    exit(1);
}

$token = $loginResult['token'];
echo "✅ Got authentication token: " . substr($token, 0, 20) . "...\n\n";

// Now let's try to create a product
$productData = [
    'nombre' => 'API Test Product ' . time(),
    'descripcion' => 'A test product created via API',
    'precio' => 25.50,
    'categoria_id' => 2,
    'tiempo_preparacion_min' => 15
];

echo "Product data to send:\n";
echo json_encode($productData, JSON_PRETTY_PRINT) . "\n\n";

$productUrl = 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api/productos';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $productUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

echo "Making API call to create product...\n";
$productResponse = curl_exec($ch);
$productHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Product Creation Response (HTTP $productHttpCode):\n";
if ($curlError) {
    echo "CURL Error: $curlError\n";
}
echo $productResponse . "\n\n";

// Try to decode the response
$productResult = json_decode($productResponse, true);
if ($productResult) {
    echo "Decoded response:\n";
    print_r($productResult);
} else {
    echo "❌ Failed to decode JSON response\n";
    echo "Raw response: " . $productResponse . "\n";
}