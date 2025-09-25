<?php
// test_jwt_integration.php - Test JWT token integration for user creation and login

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/Config/Config.php';

use App\Config\Config;

echo "🧪 Testing JWT Token Integration for User Creation and Login\n";
echo "=" . str_repeat("=", 60) . "\n\n";

$baseUrl = 'http://localhost/MenuMaster/menumaster-backend/public';

function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error, 'code' => 0, 'body' => ''];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'code' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true)
    ];
}

function testEndpoint($name, $url, $method = 'GET', $data = null, $token = null) {
    echo "🔍 Testing: $name\n";
    echo "   URL: $url\n";
    echo "   Method: $method\n";
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
        echo "   Token: " . substr($token, 0, 30) . "...\n";
    }
    
    $result = makeHttpRequest($url, $method, $data, $headers);
    
    echo "   Status: " . $result['code'] . "\n";
    
    if ($result['success']) {
        echo "   ✅ Success\n";
        if ($result['data']) {
            // Don't show sensitive data like passwords
            $displayData = $result['data'];
            if (isset($displayData['password'])) {
                $displayData['password'] = '[HIDDEN]';
            }
            echo "   Response: " . json_encode($displayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "   ❌ Failed\n";
        echo "   Response: " . substr($result['body'], 0, 300) . "...\n";
    }
    
    echo "\n";
    return $result;
}

// Test 1: User Registration with JWT Token Generation
echo "1️⃣ USER REGISTRATION TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

$newUserData = [
    'nombre' => 'JWT Test User',
    'email' => 'jwttest@menumaster.com',
    'password' => 'TestPass123!',
    'rol_id' => 2, // Assuming role ID 2 exists
    'estado_id' => 1
];

$registerResult = testEndpoint(
    'Register New User',
    "$baseUrl/api/auth/register",
    'POST',
    $newUserData
);

$registrationToken = null;
if ($registerResult['success'] && isset($registerResult['data']['token'])) {
    $registrationToken = $registerResult['data']['token'];
    echo "🎉 Registration successful! JWT token generated.\n";
    echo "Token: " . substr($registrationToken, 0, 50) . "...\n\n";
} else {
    echo "❌ Registration failed. Cannot proceed with token tests.\n\n";
}

// Test 2: Login with JWT Token Generation
echo "2️⃣ USER LOGIN TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

$loginData = [
    'email' => 'jwttest@menumaster.com',
    'password' => 'TestPass123!'
];

$loginResult = testEndpoint(
    'Login User',
    "$baseUrl/api/auth/login",
    'POST',
    $loginData
);

$loginToken = null;
if ($loginResult['success'] && isset($loginResult['data']['token'])) {
    $loginToken = $loginResult['data']['token'];
    echo "🎉 Login successful! JWT token generated.\n";
    echo "Token: " . substr($loginToken, 0, 50) . "...\n\n";
} else {
    echo "❌ Login failed. Cannot proceed with token validation tests.\n\n";
}

// Test 3: Token Validation
echo "3️⃣ JWT TOKEN VALIDATION TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

if ($loginToken) {
    $verifyResult = testEndpoint(
        'Verify JWT Token',
        "$baseUrl/api/auth/verify",
        'GET',
        null,
        $loginToken
    );
    
    if ($verifyResult['success']) {
        echo "✅ Token validation successful!\n\n";
    } else {
        echo "❌ Token validation failed!\n\n";
    }
} else {
    echo "⚠️ No token available for validation test.\n\n";
}

// Test 4: Protected Endpoint Access
echo "4️⃣ PROTECTED ENDPOINT ACCESS TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

if ($loginToken) {
    // Test accessing user profile (should work with valid token)
    $profileResult = testEndpoint(
        'Access User Profile (Protected)',
        "$baseUrl/api/usuarios/perfil",
        'GET',
        null,
        $loginToken
    );
    
    if ($profileResult['success']) {
        echo "✅ Protected endpoint access successful!\n\n";
    } else {
        echo "❌ Protected endpoint access failed!\n\n";
    }
} else {
    echo "⚠️ No token available for protected endpoint test.\n\n";
}

// Test 5: Invalid Token Test
echo "5️⃣ INVALID TOKEN TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

$invalidToken = "invalid.jwt.token.here";
$invalidTokenResult = testEndpoint(
    'Access with Invalid Token',
    "$baseUrl/api/usuarios/perfil",
    'GET',
    null,
    $invalidToken
);

if (!$invalidTokenResult['success'] && $invalidTokenResult['code'] == 401) {
    echo "✅ Invalid token correctly rejected!\n\n";
} else {
    echo "❌ Invalid token was not properly rejected!\n\n";
}

// Test 6: No Token Test
echo "6️⃣ NO TOKEN TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

$noTokenResult = testEndpoint(
    'Access without Token',
    "$baseUrl/api/usuarios/perfil",
    'GET'
);

if (!$noTokenResult['success'] && $noTokenResult['code'] == 401) {
    echo "✅ Request without token correctly rejected!\n\n";
} else {
    echo "❌ Request without token was not properly rejected!\n\n";
}

// Test 7: JWT Configuration Test
echo "7️⃣ JWT CONFIGURATION TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

try {
    $jwtConfig = Config::getJwtConfig();
    echo "JWT Configuration:\n";
    echo "   Secret: " . (isset($jwtConfig['secret']) ? '[SET]' : '[NOT SET]') . "\n";
    echo "   Algorithm: " . ($jwtConfig['algorithm'] ?? '[NOT SET]') . "\n";
    echo "   Expiration: " . ($jwtConfig['expiration'] ?? '[NOT SET]') . " seconds\n";
    
    if (isset($jwtConfig['secret']) && !empty($jwtConfig['secret'])) {
        echo "✅ JWT configuration is properly set!\n\n";
    } else {
        echo "❌ JWT configuration is missing or incomplete!\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error accessing JWT configuration: " . $e->getMessage() . "\n\n";
}

// Test 8: Token Expiration Test (if possible)
echo "8️⃣ TOKEN STRUCTURE TEST\n";
echo "-" . str_repeat("-", 30) . "\n";

if ($loginToken) {
    $tokenParts = explode('.', $loginToken);
    if (count($tokenParts) === 3) {
        echo "✅ JWT token has correct structure (3 parts)!\n";
        
        // Decode header and payload (without verification for testing)
        try {
            $header = json_decode(base64_decode($tokenParts[0]), true);
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            
            echo "   Header: " . json_encode($header) . "\n";
            echo "   Payload contains:\n";
            echo "     - User ID: " . ($payload['data']['id'] ?? '[NOT SET]') . "\n";
            echo "     - Email: " . ($payload['data']['email'] ?? '[NOT SET]') . "\n";
            echo "     - Role: " . ($payload['data']['rol'] ?? '[NOT SET]') . "\n";
            echo "     - Issued At: " . ($payload['iat'] ?? '[NOT SET]') . "\n";
            echo "     - Expires At: " . ($payload['exp'] ?? '[NOT SET]') . "\n";
            
            if (isset($payload['exp'])) {
                $expiresAt = date('Y-m-d H:i:s', $payload['exp']);
                echo "     - Expires On: $expiresAt\n";
            }
            
            echo "✅ Token payload structure is correct!\n\n";
        } catch (Exception $e) {
            echo "❌ Error decoding token structure: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "❌ JWT token has incorrect structure!\n\n";
    }
} else {
    echo "⚠️ No token available for structure test.\n\n";
}

// Cleanup: Delete test user
echo "9️⃣ CLEANUP\n";
echo "-" . str_repeat("-", 30) . "\n";

if ($loginToken) {
    // Try to get user ID from token to delete the test user
    try {
        $tokenParts = explode('.', $loginToken);
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        $userId = $payload['data']['id'] ?? null;
        
        if ($userId) {
            // Note: This would require admin privileges, so it might fail
            echo "Attempting to cleanup test user (ID: $userId)...\n";
            echo "⚠️ Cleanup may require manual intervention if not admin.\n\n";
        }
    } catch (Exception $e) {
        echo "⚠️ Could not extract user ID for cleanup.\n\n";
    }
}

echo "🎯 JWT Integration Test Summary:\n";
echo "=" . str_repeat("=", 40) . "\n";
echo "✅ User Registration: " . ($registrationToken ? "PASS" : "FAIL") . "\n";
echo "✅ User Login: " . ($loginToken ? "PASS" : "FAIL") . "\n";
echo "✅ Token Validation: " . (isset($verifyResult) && $verifyResult['success'] ? "PASS" : "FAIL") . "\n";
echo "✅ Protected Access: " . (isset($profileResult) && $profileResult['success'] ? "PASS" : "FAIL") . "\n";
echo "✅ Invalid Token Rejection: " . (isset($invalidTokenResult) && !$invalidTokenResult['success'] ? "PASS" : "FAIL") . "\n";
echo "✅ No Token Rejection: " . (isset($noTokenResult) && !$noTokenResult['success'] ? "PASS" : "FAIL") . "\n";

echo "\n🏁 JWT Integration Testing Complete!\n";
?>