<?php

// test_simple_endpoint.php - Test basic API endpoints

echo "Testing basic API endpoints...\n\n";

// Test auth endpoint (should work)
echo "1. Testing auth endpoint (login):\n";
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
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            echo "  $header\n";
        }
    }
} else {
    echo "✅ Login successful!\n";
    $loginResponse = json_decode($loginResult, true);
    if (isset($loginResponse['token'])) {
        echo "Token received: " . substr($loginResponse['token'], 0, 50) . "...\n";
    }
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test a simple GET endpoint without authentication
echo "2. Testing simple GET endpoint (should fail with 401):\n";
$url = 'http://localhost/MenuMaster/menumaster-backend/public/api/usuarios';

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'GET'
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "❌ Request failed (expected for unauthorized access)\n";
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            echo "  $header\n";
        }
    }
} else {
    echo "✅ Request successful (unexpected):\n";
    echo $result . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test direct access to router
echo "3. Testing direct router access:\n";
$routerUrl = 'http://localhost/MenuMaster/menumaster-backend/public/index.php';

$routerOptions = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'GET'
    ]
];

$routerContext = stream_context_create($routerOptions);
$routerResult = file_get_contents($routerUrl, false, $routerContext);

if ($routerResult === FALSE) {
    echo "❌ Router access failed\n";
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            echo "  $header\n";
        }
    }
} else {
    echo "✅ Router accessible:\n";
    echo substr($routerResult, 0, 200) . "...\n";
}