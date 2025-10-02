<?php
// Debug JWT creation and validation
require_once 'vendor/autoload.php';
require_once 'App/config/Config.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\Config;

echo "=== JWT CREATION AND VALIDATION TEST ===\n";

// Get JWT config
$jwtConfig = Config::getJwtConfig();
echo "JWT Secret: " . $jwtConfig['secret'] . "\n";
echo "JWT Algorithm: " . $jwtConfig['algorithm'] . "\n\n";

// Create a test token
$issuedAt = time();
$expirationTime = $issuedAt + $jwtConfig['expiration'];

$payload = [
    "iat" => $issuedAt,
    "exp" => $expirationTime,
    "iss" => "MenuMaster",
    "aud" => "MenuMaster",
    "data" => [
        "id" => 1,
        "nombre" => "Administrador",
        "email" => "admin@menumaster.com",
        "rol_id" => 1
    ]
];

echo "Creating token with payload:\n";
print_r($payload);

// Create token
$token = JWT::encode($payload, $jwtConfig['secret'], $jwtConfig['algorithm']);
echo "\nGenerated token: " . substr($token, 0, 50) . "...\n\n";

// Try to validate the same token
echo "Attempting to validate the token we just created...\n";
try {
    $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
    echo "✓ Token validation successful!\n";
    echo "Decoded data:\n";
    print_r($decoded);
} catch (Exception $e) {
    echo "✗ Token validation failed: " . $e->getMessage() . "\n";
    echo "Error class: " . get_class($e) . "\n";
}