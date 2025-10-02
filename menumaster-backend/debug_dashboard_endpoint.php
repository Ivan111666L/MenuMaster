<?php
// Debug script for dashboard endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/App/config/ConexionDb.php';
require_once __DIR__ . '/App/Controllers/DashboardController.php';
require_once __DIR__ . '/App/Middleware/AuthMiddleware.php';

use App\Config\ConexionDb;
use App\Controllers\DashboardController;
use App\Middleware\AuthMiddleware;

try {
    echo "=== Debug Dashboard Endpoint ===\n";
    
    echo "\n1. Testing database connection...\n";
    $db = ConexionDb::getConnection();
    echo "✓ Database connected successfully\n";
    
    echo "\n2. Testing JWT token validation...\n";
    // Use the token from the login response
    $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTkzNjcxNzEsImV4cCI6MTc1OTM3MDc3MSwiaXNzIjoiTWVudU1hc3RlciIsImRhdGEiOnsiaWQiOjgsImVtYWlsIjoiY2FybEBnbWFpbC5jb20iLCJyb2wiOiJBZG1pbmlzdHJhZG9yIiwicm9sX2lkIjoxfX0.1zgrcYC8NOqzgXKdK6feAyzqe1QXoyR5Gowxo5txlGk";
    
    // Set the authorization header for testing
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->getCurrentUser();
    
    if ($user) {
        echo "✓ Token validation successful\n";
        echo "User: " . json_encode($user) . "\n";
    } else {
        echo "✗ Token validation failed\n";
        exit;
    }
    
    echo "\n3. Testing DashboardController directly...\n";
    $controller = new DashboardController($db);
    
    // Capture the output
    ob_start();
    try {
        $controller->getSummary();
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