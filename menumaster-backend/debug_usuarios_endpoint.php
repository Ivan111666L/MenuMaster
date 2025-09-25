<?php

// debug_usuarios_endpoint.php - Debug the usuarios endpoint specifically

// First, get a valid token
$loginUrl = 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/login';
$loginData = [
    'email' => 'test@example.com',
    'password' => 'test123'
];

$loginOptions = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($loginData)
    ]
];

$loginContext = stream_context_create($loginOptions);
$loginResult = file_get_contents($loginUrl, false, $loginContext);

if ($loginResult === FALSE) {
    echo "❌ Login failed\n";
    exit(1);
}

$loginResponse = json_decode($loginResult, true);
if (!$loginResponse || !isset($loginResponse['token'])) {
    echo "❌ No token received from login\n";
    exit(1);
}

$token = $loginResponse['token'];
echo "✅ Login successful, token: " . substr($token, 0, 50) . "...\n\n";

// Now test the usuarios endpoint with detailed error capture
echo "Testing /api/usuarios endpoint...\n";

$url = 'http://localhost/MenuMaster/menumaster-backend/public/api/usuarios';

// Use cURL for better error handling
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, fopen('php://temp', 'w+'));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($error) {
    echo "cURL Error: $error\n";
}

curl_close($ch);

// Also try direct file access to see what happens
echo "\n" . str_repeat("-", 50) . "\n";
echo "Testing direct file access to usuarios_api.php...\n";

// Set up environment for direct access
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/MenuMaster/menumaster-backend/public/api/usuarios';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

// Capture output
ob_start();
try {
    // Include the necessary files
    define('BASE_PATH', dirname(__FILE__));
    require_once BASE_PATH . '/App/config/conexionDb.php';
    
    $db = \App\Config\ConexionDb::getConnection();
    
    // Include the usuarios API directly
    include BASE_PATH . '/routes/usuarios_api.php';
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
$output = ob_get_clean();

echo "Direct access output:\n";
echo $output;