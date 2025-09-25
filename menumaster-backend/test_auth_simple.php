<?php
// Simple authentication test
echo "=== TESTING AUTHENTICATION ===\n";

// Test data for login
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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}

echo "Response: $response\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['token'])) {
        echo "✅ Authentication successful!\n";
        echo "Token: " . substr($data['token'], 0, 50) . "...\n";
    } else {
        echo "❌ No token in response\n";
    }
} else {
    echo "❌ Authentication failed\n";
}