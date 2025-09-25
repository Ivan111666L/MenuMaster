<?php
// test_auth_verify_endpoint.php - Test the auth/verify endpoint specifically

require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Testing Auth Verify Endpoint\n";
echo "=" . str_repeat("=", 40) . "\n\n";

function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'code' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true),
        'error' => $error
    ];
}

$baseUrl = 'http://localhost/MenuMaster/menumaster-backend/public';

// Step 1: Login to get a token
echo "1. Getting token via login:\n";
echo "-" . str_repeat("-", 30) . "\n";

$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

$loginResult = makeHttpRequest(
    "$baseUrl/api/auth/login",
    'POST',
    $loginData,
    ['Content-Type: application/json']
);

if ($loginResult['success'] && isset($loginResult['data']['token'])) {
    $token = $loginResult['data']['token'];
    echo "✅ Login successful!\n";
    echo "Token: " . substr($token, 0, 50) . "...\n\n";
    
    // Step 2: Test the auth/verify endpoint
    echo "2. Testing auth/verify endpoint:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $verifyResult = makeHttpRequest(
        "$baseUrl/api/auth/verify",
        'GET',
        null,
        [
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        ]
    );
    
    echo "Status Code: " . $verifyResult['code'] . "\n";
    echo "Response: " . json_encode($verifyResult['data'], JSON_PRETTY_PRINT) . "\n";
    
    if ($verifyResult['success']) {
        echo "✅ Auth verify endpoint working correctly!\n";
    } else {
        echo "❌ Auth verify endpoint failed!\n";
    }
    
    echo "\n";
    
    // Step 3: Test with invalid token
    echo "3. Testing with invalid token:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $invalidResult = makeHttpRequest(
        "$baseUrl/api/auth/verify",
        'GET',
        null,
        [
            'Content-Type: application/json',
            "Authorization: Bearer invalid.token.here"
        ]
    );
    
    echo "Status Code: " . $invalidResult['code'] . "\n";
    echo "Response: " . json_encode($invalidResult['data'], JSON_PRETTY_PRINT) . "\n";
    
    if (!$invalidResult['success'] && $invalidResult['code'] == 401) {
        echo "✅ Invalid token correctly rejected!\n";
    } else {
        echo "❌ Invalid token handling failed!\n";
    }
    
    echo "\n";
    
    // Step 4: Test without token
    echo "4. Testing without token:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $noTokenResult = makeHttpRequest(
        "$baseUrl/api/auth/verify",
        'GET',
        null,
        ['Content-Type: application/json']
    );
    
    echo "Status Code: " . $noTokenResult['code'] . "\n";
    echo "Response: " . json_encode($noTokenResult['data'], JSON_PRETTY_PRINT) . "\n";
    
    if (!$noTokenResult['success'] && $noTokenResult['code'] == 401) {
        echo "✅ Missing token correctly rejected!\n";
    } else {
        echo "❌ Missing token handling failed!\n";
    }
    
} else {
    echo "❌ Login failed, cannot test verify endpoint\n";
    echo "Login response: " . json_encode($loginResult['data'], JSON_PRETTY_PRINT) . "\n";
}

echo "\n🏁 Auth Verify Endpoint Test Complete!\n";
?>