<?php
// debug_frontend_api_call.php
// This script simulates the exact API call that the frontend is making

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set headers to match what the frontend sends
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo "=== DEBUG: Frontend API Call Simulation ===\n";

// Test 1: Check if we can get a token from login
echo "\n1. Testing login to get a fresh token...\n";

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
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Code: $loginHttpCode\n";
echo "Login Response: $loginResponse\n";

if ($loginHttpCode !== 200) {
    echo "ERROR: Login failed!\n";
    exit(1);
}

$loginData = json_decode($loginResponse, true);
if (!$loginData || !isset($loginData['token'])) {
    echo "ERROR: No token received from login!\n";
    exit(1);
}

$token = $loginData['token'];
echo "Token received: " . substr($token, 0, 50) . "...\n";

// Test 2: Test dashboard API call with the token
echo "\n2. Testing dashboard API call with token...\n";

$dashboardUrl = 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api/dashboard/summary';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dashboardUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$dashboardResponse = curl_exec($ch);
$dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Dashboard HTTP Code: $dashboardHttpCode\n";
echo "Dashboard Response: $dashboardResponse\n";

if ($curlError) {
    echo "CURL Error: $curlError\n";
}

// Test 3: Test permissions API call with the token
echo "\n3. Testing permissions API call with token...\n";

$permissionsUrl = 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api/permisos/current-user';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $permissionsUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$permissionsResponse = curl_exec($ch);
$permissionsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Permissions HTTP Code: $permissionsHttpCode\n";
echo "Permissions Response: $permissionsResponse\n";

if ($curlError) {
    echo "CURL Error: $curlError\n";
}

// Test 4: Check what the frontend is actually sending
echo "\n4. Checking request headers and token format...\n";

// Decode the JWT token to see its contents
$tokenParts = explode('.', $token);
if (count($tokenParts) === 3) {
    $header = json_decode(base64_decode($tokenParts[0]), true);
    $payload = json_decode(base64_decode($tokenParts[1]), true);
    
    echo "JWT Header: " . json_encode($header, JSON_PRETTY_PRINT) . "\n";
    echo "JWT Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
    
    // Check if token is expired
    $now = time();
    if (isset($payload['exp']) && $payload['exp'] < $now) {
        echo "WARNING: Token is expired!\n";
        echo "Token exp: " . $payload['exp'] . " (" . date('Y-m-d H:i:s', $payload['exp']) . ")\n";
        echo "Current time: $now (" . date('Y-m-d H:i:s', $now) . ")\n";
    } else {
        echo "Token is valid and not expired.\n";
    }
}

echo "\n=== DEBUG COMPLETE ===\n";
?>