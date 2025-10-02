<?php
// Debug script to test permissions endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Definir BASE_PATH
define('BASE_PATH', __DIR__);

// Include necessary files
require_once BASE_PATH . '/App/config/Config.php';
require_once BASE_PATH . '/App/config/conexionDb.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Middleware/RolMiddleware.php';
require_once BASE_PATH . '/App/Controllers/Controller.php';
require_once BASE_PATH . '/App/Controllers/PermisosController.php';

use App\Config\Config;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Controllers\PermisosController;

try {
    echo "=== DEBUG PERMISSIONS ENDPOINT ===\n";
    
    // Simular headers de autenticación
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTkzNjYyMDksImV4cCI6MTc1OTM2OTgwOSwiaXNzIjoiTWVudU1hc3RlciIsImF1ZCI6Ik1lbnVNYXN0ZXIiLCJkYXRhIjp7ImlkIjoxLCJub21icmUiOiJBZG1pbmlzdHJhZG9yIiwiZW1haWwiOiJhZG1pbkBtZW51bWFzdGVyLmNvbSIsInJvbF9pZCI6MX19.Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8Ej8';
    
    echo "1. Testing AuthMiddleware...\n";
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->getCurrentUser();
    
    if ($user) {
        echo "✓ User authenticated: " . json_encode($user) . "\n";
    } else {
        echo "✗ Authentication failed\n";
        exit;
    }
    
    echo "\n2. Testing RolMiddleware...\n";
    $rolMiddleware = new RolMiddleware();
    $permissions = $rolMiddleware->getCurrentUserPermissions();
    
    echo "✓ Permissions retrieved: " . json_encode($permissions) . "\n";
    
    echo "\n3. Testing PermisosController...\n";
    $controller = new PermisosController();
    
    // Capturar la salida
    ob_start();
    $controller->getCurrentUserPermisos();
    $output = ob_get_clean();
    
    echo "✓ Controller output: " . $output . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}