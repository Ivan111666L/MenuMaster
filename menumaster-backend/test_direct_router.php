<?php
// Test direct router call
echo "=== TESTING DIRECT ROUTER CALL ===\n";

// Set up environment
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Simulate request for auth/login
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['SCRIPT_NAME'] = '/MenuMaster/menumaster-backend/public/index.php';
$_SERVER['REQUEST_URI'] = '/MenuMaster/menumaster-backend/public/index.php/api/auth/login';

// Simulate POST data
$_POST = [];
file_put_contents('php://input', json_encode([
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
]));

echo "Simulated REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Simulated SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Simulated REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";

try {
    // Include the router
    require_once BASE_PATH . '/routes/router.php';
    echo "✅ Router executed successfully\n";
} catch (Exception $e) {
    echo "❌ Router error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}