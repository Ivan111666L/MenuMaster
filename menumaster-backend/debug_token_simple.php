<?php
// Simple token debug script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include necessary files
require_once __DIR__ . '/App/config/Config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\Config;

// The token from our fresh login test
$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTkzNjY3MTYsImV4cCI6MTc1OTM3MDMxNiwiaXNzIjoiTWVudU1hc3RlciIsImF1ZCI6Ik1lbnVNYXN0ZXIiLCJkYXRhIjp7ImlkIjoxLCJub21icmUiOiJBZG1pbmlzdHJhZG9yIiwiZW1haWwiOiJhZG1pbkBtZW51bWFzdGVyLmNvbSIsInJvbF9pZCI6MX19.Ej7Ey8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8";

echo "=== TOKEN DEBUG ===\n";
echo "Token: " . substr($token, 0, 50) . "...\n\n";

try {
    // Get JWT config
    $jwtConfig = Config::getJwtConfig();
    echo "JWT Secret: " . $jwtConfig['secret'] . "\n";
    echo "JWT Algorithm: " . $jwtConfig['algorithm'] . "\n";
    echo "JWT Expiration: " . $jwtConfig['expiration'] . " seconds\n\n";
    
    // Try to decode the token
    echo "Attempting to decode token...\n";
    $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
    
    echo "✓ Token decoded successfully!\n";
    echo "Token data:\n";
    print_r($decoded);
    
    // Check expiration
    $now = time();
    echo "\nCurrent time: " . $now . " (" . date('Y-m-d H:i:s', $now) . ")\n";
    echo "Token exp: " . $decoded->exp . " (" . date('Y-m-d H:i:s', $decoded->exp) . ")\n";
    
    if ($now > $decoded->exp) {
        echo "✗ Token is EXPIRED\n";
    } else {
        echo "✓ Token is still valid\n";
    }
    
} catch (Exception $e) {
    echo "✗ Token decode failed: " . $e->getMessage() . "\n";
    echo "Error class: " . get_class($e) . "\n";
}