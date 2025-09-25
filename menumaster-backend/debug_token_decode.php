<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/config/conexionDb.php';
require_once BASE_PATH . '/app/config/Config.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/vendor/autoload.php';

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

// Test token decoding
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

// Login to get token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/MenuMaster/menumaster-backend/public/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Status: $httpStatus\n";
echo "Login Response: $response\n\n";

if ($httpStatus === 200) {
    $loginResult = json_decode($response, true);
    $token = $loginResult['token'] ?? null;
    
    if ($token) {
        echo "Token: " . substr($token, 0, 50) . "...\n\n";
        
        try {
            // Test decoding
            $payload = AuthController::decodeTokenData($token);
            echo "Decoded payload:\n";
            print_r($payload);
            
            echo "\nRole ID from payload: " . ($payload['data']['rol_id'] ?? 'NOT FOUND') . "\n";
            echo "Expected role ID: 1\n";
            echo "Match: " . (($payload['data']['rol_id'] ?? null) === 1 ? 'YES' : 'NO') . "\n";
            
        } catch (Exception $e) {
            echo "Error decoding token: " . $e->getMessage() . "\n";
        }
    }
}