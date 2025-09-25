<?php
// debug_auth_endpoints.php - Debug auth endpoints with detailed error reporting

require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Debugging Auth Endpoints\n";
echo "=" . str_repeat("=", 40) . "\n\n";

// Test 1: Direct registration test
echo "1. Testing Registration Endpoint Directly:\n";
echo "-" . str_repeat("-", 40) . "\n";

$registrationUrl = 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/register';
$registrationData = [
    'nombre' => 'Debug Test User',
    'email' => 'debugtest@menumaster.com',
    'password' => 'DebugPass123!',
    'rol_id' => 2,
    'estado_id' => 1
];

echo "URL: $registrationUrl\n";
echo "Data: " . json_encode($registrationData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $registrationUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registrationData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
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

echo "\n" . str_repeat("=", 50) . "\n\n";

// Test 2: Test with existing user credentials
echo "2. Testing Login with Existing User:\n";
echo "-" . str_repeat("-", 40) . "\n";

$loginUrl = 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/login';
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

echo "URL: $loginUrl\n";
echo "Data: " . json_encode($loginData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
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

echo "\n" . str_repeat("=", 50) . "\n\n";

// Test 3: Check if auth routes are accessible
echo "3. Testing Route Accessibility:\n";
echo "-" . str_repeat("-", 40) . "\n";

$testUrls = [
    'http://localhost/MenuMaster/menumaster-backend/public/api/auth',
    'http://localhost/MenuMaster/menumaster-backend/public/api',
    'http://localhost/MenuMaster/menumaster-backend/public'
];

foreach ($testUrls as $url) {
    echo "Testing: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "  Status: $httpCode\n";
    
    curl_close($ch);
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Test 4: Check server logs or error details
echo "4. Environment Check:\n";
echo "-" . str_repeat("-", 40) . "\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Current Working Directory: " . getcwd() . "\n";
echo "Script Directory: " . __DIR__ . "\n";

// Check if required files exist
$requiredFiles = [
    __DIR__ . '/App/Controllers/AuthController.php',
    __DIR__ . '/App/Models/UsuarioModel.php',
    __DIR__ . '/App/Models/RolModel.php',
    __DIR__ . '/App/Config/Config.php',
    __DIR__ . '/routes/router.php'
];

echo "\nFile Existence Check:\n";
foreach ($requiredFiles as $file) {
    $exists = file_exists($file) ? '✅' : '❌';
    echo "  $exists $file\n";
}

echo "\n🏁 Debug Complete!\n";
?>