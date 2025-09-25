<?php
// Set environment variables for database connection
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'menu_master';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_CHARSET'] = 'utf8mb4';

require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';
require_once 'App/models/UsuarioModel.php';
require_once 'App/Config/Config.php';
require_once 'App/Middleware/AuthMiddleware.php';

use App\Config\ConexionDb;
use App\Config\Config;
use App\Middleware\AuthMiddleware;

try {
    echo "🔍 Debug Token Validation\n";
    echo "========================\n";
    
    // First, let's create a test user and get a token
    $registerUrl = "http://localhost/MenuMaster/menumaster-backend/public/api/auth/register";
    $registerData = [
        "nombre" => "Token Test User " . time(),
        "email" => "tokentest" . time() . "@menumaster.com",
        "password" => "TokenTest123!",
        "rol_id" => 2,
        "estado_id" => 1
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $registerUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Registration Response (HTTP $httpCode):\n";
    echo $response . "\n\n";
    
    $registerResult = json_decode($response, true);
    
    if ($httpCode === 201 && isset($registerResult['token'])) {
        $token = $registerResult['token'];
        $userId = $registerResult['usuario']['id'];
        
        echo "✅ Got token: " . substr($token, 0, 50) . "...\n";
        echo "✅ User ID: $userId\n\n";
        
        // Now let's test token validation directly
        echo "Testing token validation with AuthMiddleware:\n";
        echo "============================================\n";
        
        $authMiddleware = new AuthMiddleware();
        
        // Test 1: Direct token validation
        echo "1. Direct token validation:\n";
        $validationResult = $authMiddleware->validateToken($token);
        
        if ($validationResult) {
            echo "✅ Token validation successful\n";
            echo "User data: " . json_encode($validationResult) . "\n";
        } else {
            echo "❌ Token validation failed\n";
        }
        
        echo "\n";
        
        // Test 2: Check user by ID directly
        echo "2. Direct user lookup by ID ($userId):\n";
        $userById = $authMiddleware->getUserById($userId);
        
        if ($userById) {
            echo "✅ User found by ID\n";
            echo "User data: " . json_encode($userById) . "\n";
            echo "Estado ID: " . ($userById['estado_id'] ?? 'NOT SET') . "\n";
        } else {
            echo "❌ User not found by ID\n";
        }
        
        echo "\n";
        
        // Test 3: Check database directly
        echo "3. Direct database query:\n";
        $db = ConexionDb::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dbUser) {
            echo "✅ User found in database\n";
            echo "Database user data: " . json_encode($dbUser) . "\n";
        } else {
            echo "❌ User not found in database\n";
        }
        
        // Cleanup
        echo "\n4. Cleanup:\n";
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        echo "✅ Test user deleted\n";
        
    } else {
        echo "❌ Registration failed, cannot test token validation\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}