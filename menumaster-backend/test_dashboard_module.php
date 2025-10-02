<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';
require_once 'App/Controllers/DashboardController.php';
require_once 'App/Controllers/AuthController.php';

use App\Config\ConexionDb;
use App\Controllers\DashboardController;
use App\Controllers\AuthController;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    echo "✅ Database connected successfully\n";
    
    // Create test user and get JWT token
    $authController = new AuthController();
    
    // Get admin user
    $stmt = $db->prepare("SELECT u.*, r.nombre as role_name, r.id as role_id FROM usuarios u 
                         LEFT JOIN roles r ON u.rol_id = r.id 
                         WHERE u.email = 'admin@menumaster.com' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Admin user not found\n";
        exit(1);
    }
    
    echo "✅ Using admin user: {$user['nombre']} (ID: {$user['id']})\n";
    
    // Generate JWT token using reflection
    $reflection = new ReflectionClass($authController);
    $generateTokenMethod = $reflection->getMethod('generateToken');
    $generateTokenMethod->setAccessible(true);
    $token = $generateTokenMethod->invoke($authController, $user);
    
    echo "✅ JWT token generated\n";
    
    // Set authorization header
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    
    // Test Dashboard Controller
    echo "\n=== Testing Dashboard Module ===\n";
    
    $dashboardController = new DashboardController($db);
    
    // Test 1: Get dashboard summary
    echo "Testing getSummary()...\n";
    try {
        ob_start();
        $dashboardController->getSummary();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Dashboard summary retrieved successfully\n";
            echo "   - Active orders: " . ($response['data']['pedidosActivos'] ?? 'N/A') . "\n";
            echo "   - Today's sales: $" . ($response['data']['ventasDia'] ?? 'N/A') . "\n";
            echo "   - Occupied tables: " . ($response['data']['mesasOcupadas'] ?? 'N/A') . "\n";
            echo "   - Total tables: " . ($response['data']['mesasTotales'] ?? 'N/A') . "\n";
            echo "   - Low stock items: " . ($response['data']['inventarioBajo'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Failed to get dashboard summary\n";
            echo "Response: " . $output . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing getSummary: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Dashboard Module Test Complete ===\n";
    
} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}