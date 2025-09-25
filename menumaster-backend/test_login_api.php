<?php




// test_login_api.php - Test the login API endpoint

$url = 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/login';
$data = [
    'email' => 'test@example.com',
    'password' => 'test123'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);

echo "Testing login API...\n";
echo "URL: $url\n";
echo "Data: " . json_encode($data) . "\n\n";

$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "❌ Request failed\n";
    
    // Get HTTP response headers
    if (isset($http_response_header)) {
        echo "Response headers:\n";
        foreach ($http_response_header as $header) {
            echo "  $header\n";
        }
    }
} else {
    echo "✅ Request successful!\n";
    echo "Response:\n";
    
    // Pretty print JSON response
    $decoded = json_decode($result, true);
    if ($decoded) {
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $result . "\n";
    }
    
    // Show response headers
    if (isset($http_response_header)) {
        echo "\nResponse headers:\n";
        foreach ($http_response_header as $header) {
            echo "  $header\n";
        }
    }
}