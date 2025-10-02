<?php
// Debug script to test the permissions endpoint directly
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/config/ConexionDb.php';
require_once __DIR__ . '/App/Controllers/PermisosController.php';
require_once __DIR__ . '/App/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/App/Middleware/RolMiddleware.php';

use App\Config\ConexionDb;
use App\Controllers\PermisosController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== PERMISSIONS ENDPOINT DEBUG ===\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $db = ConexionDb::getConnection();
    echo "✓ Database connected successfully\n";
    
    // Test JWT token (use a fresh one)
    echo "\n2. Testing JWT token validation...\n";
    $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTkzNjY4NzcsImV4cCI6MTc1OTM3MDQ3NywiaXNzIjoiTWVudU1hc3RlciIsImRhdGEiOnsiaWQiOjEsImVtYWlsIjoiYWRtaW5AbWVudW1hc3Rlci5jb20iLCJyb2wiOiJBZG1pbmlzdHJhZG9yIiwicm9sX2lkIjoxfX0.MzqZD8BjhD5ONBsRNoHQAcg1H8odu_IAjG9Kakr3moM";
    
    // Simulate the Authorization header
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
    
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->getCurrentUser();
    
    if ($user) {
        echo "✓ Token validation successful\n";
        echo "User: " . json_encode($user) . "\n";
    } else {
        echo "✗ Token validation failed\n";
        exit;
    }
    
    echo "\n3. Testing RolMiddleware...\n";
    $rolMiddleware = new RolMiddleware();
    $permissions = $rolMiddleware->getCurrentUserPermissions();
    
    echo "✓ Permissions retrieved: " . json_encode($permissions) . "\n";
    
    echo "\n4. Testing PermisosController directly...\n";
    $controller = new PermisosController();
    
    // Capture the output
    ob_start();
    try {
        $controller->getCurrentUserPermisos();
        $output = ob_get_clean();
        echo "✓ Controller executed successfully\n";
        echo "Output: " . $output . "\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "✗ Controller failed: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}