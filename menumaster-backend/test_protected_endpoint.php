<?php

// test_protected_endpoint.php - Test protected endpoints with JWT token

// First, get the token from login
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
echo "✅ Login successful, token received\n";
echo "Token: " . substr($token, 0, 50) . "...\n\n";

// Test protected endpoints
$protectedEndpoints = [
    'GET /api/usuarios' => [
        'url' => 'http://localhost/MenuMaster/menumaster-backend/public/api/usuarios',
        'method' => 'GET'
    ],
    'GET /api/roles' => [
        'url' => 'http://localhost/MenuMaster/menumaster-backend/public/api/roles',
        'method' => 'GET'
    ]
];

foreach ($protectedEndpoints as $name => $endpoint) {
    echo "Testing $name...\n";
    
    $options = [
        'http' => [
            'header' => "Authorization: Bearer $token\r\nContent-Type: application/json\r\n",
            'method' => $endpoint['method']
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($endpoint['url'], false, $context);
    
    if ($result === FALSE) {
        echo "❌ Request failed\n";
        if (isset($http_response_header)) {
            echo "Response headers:\n";
            foreach ($http_response_header as $header) {
                echo "  $header\n";
            }
        }
    } else {
        echo "✅ Request successful!\n";
        $decoded = json_decode($result, true);
        if ($decoded) {
            echo "Response: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Response: $result\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}