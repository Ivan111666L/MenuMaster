<?php
// Debug routing logic
echo "=== DEBUGGING ROUTING LOGIC ===\n";

// Simulate the same logic as in router.php
$basePath   = dirname('/MenuMaster/menumaster-backend/public/index.php');
$requestUri = '/MenuMaster/menumaster-backend/public/index.php/api/auth/login';

echo "basePath: '$basePath'\n";
echo "requestUri: '$requestUri'\n";

$route = $requestUri;
if (strpos($requestUri, $basePath) === 0) {
    $route = substr($requestUri, strlen($basePath));
    echo "Route after basePath removal: '$route'\n";
} else {
    echo "basePath not found at start of requestUri\n";
}

$route_parts = explode('/', trim($route, '/'));
echo "Route parts: " . json_encode($route_parts) . "\n";

// Check first part
$firstPart = $route_parts[0] ?? '';
echo "First part: '$firstPart'\n";
echo "Is 'api'? " . (($firstPart === 'api') ? 'YES' : 'NO') . "\n";

// Check resource and action
$resource = $route_parts[1] ?? null;
$action   = $route_parts[2] ?? null;

echo "Resource: '$resource'\n";
echo "Action: '$action'\n";