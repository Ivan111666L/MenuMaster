<?php
// Comprehensive API testing with admin credentials

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$baseUrl = 'http://localhost/MenuMaster/menumaster-backend/public';

function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => $response];
}

function testEndpoint($name, $url, $method = 'GET', $data = null, $token = null) {
    echo "\n🧪 Testing: $name\n";
    echo "   URL: $url\n";
    echo "   Method: $method\n";
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    $result = makeRequest($url, $method, $data, $headers);
    
    echo "   Status: " . $result['code'] . "\n";
    
    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo "   ✅ Success\n";
        $decoded = json_decode($result['body'], true);
        if ($decoded) {
            echo "   Response: " . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "   Response: " . substr($result['body'], 0, 200) . "...\n";
        }
    } else {
        echo "   ❌ Failed\n";
        echo "   Response: " . substr($result['body'], 0, 500) . "...\n";
    }
    
    return $result;
}

echo "🚀 Starting comprehensive API testing with admin credentials...\n";

// 1. Login as admin
echo "\n=== AUTHENTICATION ===\n";
$loginResult = testEndpoint(
    'Admin Login',
    "$baseUrl/api/auth/login",
    'POST',
    ['email' => 'admin@menumaster.com', 'password' => 'admin123']
);

$adminToken = null;
if ($loginResult['code'] === 200) {
    $loginData = json_decode($loginResult['body'], true);
    $adminToken = $loginData['token'] ?? null;
    echo "   🔑 Admin token obtained\n";
} else {
    echo "   ❌ Failed to get admin token, stopping tests\n";
    exit(1);
}

// 2. Test protected endpoints
echo "\n=== PROTECTED ENDPOINTS ===\n";

// Test usuarios endpoints
testEndpoint('Get All Users', "$baseUrl/api/usuarios", 'GET', null, $adminToken);
testEndpoint('Get User Profile', "$baseUrl/api/usuarios/perfil", 'GET', null, $adminToken);
testEndpoint('Get Specific User', "$baseUrl/api/usuarios/30", 'GET', null, $adminToken);

// Test roles endpoints
testEndpoint('Get All Roles', "$baseUrl/api/roles", 'GET', null, $adminToken);

// Test permisos endpoints
testEndpoint('Get All Permissions', "$baseUrl/api/permisos", 'GET', null, $adminToken);

// 3. Test CRUD operations
echo "\n=== CRUD OPERATIONS ===\n";

// Create a new user
$newUserData = [
    'nombre' => 'Test User',
    'email' => 'testuser@example.com',
    'password' => 'testpass123',
    'rol_id' => 1,
    'estado_id' => 1
];
$createResult = testEndpoint('Create New User', "$baseUrl/api/usuarios", 'POST', $newUserData, $adminToken);

$newUserId = null;
if ($createResult['code'] === 201) {
    $createData = json_decode($createResult['body'], true);
    $newUserId = $createData['id'] ?? null;
    echo "   👤 New user created with ID: $newUserId\n";
}

// Update the user if created successfully
if ($newUserId) {
    $updateData = [
        'nombre' => 'Updated Test User',
        'email' => 'updated@example.com'
    ];
    testEndpoint("Update User $newUserId", "$baseUrl/api/usuarios/$newUserId", 'PUT', $updateData, $adminToken);
    
    // Get the updated user
    testEndpoint("Get Updated User $newUserId", "$baseUrl/api/usuarios/$newUserId", 'GET', null, $adminToken);
    
    // Delete the user
    testEndpoint("Delete User $newUserId", "$baseUrl/api/usuarios/$newUserId", 'DELETE', null, $adminToken);
}

// 4. Test without authentication
echo "\n=== UNAUTHORIZED ACCESS TESTS ===\n";
testEndpoint('Unauthorized Users Access', "$baseUrl/api/usuarios", 'GET');
testEndpoint('Unauthorized Roles Access', "$baseUrl/api/roles", 'GET');

// 5. Test with regular user token
echo "\n=== REGULAR USER ACCESS TESTS ===\n";
$regularLoginResult = testEndpoint(
    'Regular User Login',
    "$baseUrl/api/auth/login",
    'POST',
    ['email' => 'test@example.com', 'password' => 'test123']
);

$regularToken = null;
if ($regularLoginResult['code'] === 200) {
    $regularLoginData = json_decode($regularLoginResult['body'], true);
    $regularToken = $regularLoginData['token'] ?? null;
    
    // Test admin-only endpoints with regular user token
    testEndpoint('Regular User - Get All Users (should fail)', "$baseUrl/api/usuarios", 'GET', null, $regularToken);
    testEndpoint('Regular User - Get Profile (should work)', "$baseUrl/api/usuarios/perfil", 'GET', null, $regularToken);
}

echo "\n🎉 API testing completed!\n";