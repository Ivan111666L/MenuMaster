<?php
require_once 'vendor/autoload.php';
// Asegurar carga de clases de constantes (EstadosMesa, EstadosPedido, etc.)
require_once 'App/config/Constantes.php';
require_once 'App/config/conexionDb.php';
require_once 'App/Controllers/PedidoController.php';
require_once 'App/Controllers/AuthController.php';
require_once 'App/Middleware/AuthMiddleware.php';

use App\Config\ConexionDb;
use App\Controllers\PedidoController;
use App\Controllers\AuthController;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    echo "Database connected successfully\n";
    
    // Get a test user for authentication
    $stmt = $db->prepare("SELECT id, nombre FROM usuarios LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "No users found in database. Creating test user...\n";
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol_id, estado_id) VALUES (?, ?, ?, 1, 1)");
        $stmt->execute(['Test User', 'test@example.com', password_hash('test123', PASSWORD_DEFAULT)]);
        $userId = $db->lastInsertId();
        echo "Test user created with ID: $userId\n";
    } else {
        $userId = $user['id'];
        echo "Using existing user: " . $user['nombre'] . " (ID: $userId)\n";
    }
    
    // Get a test mesa
    $stmt = $db->prepare("SELECT id, numero FROM mesas LIMIT 1");
    $stmt->execute();
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mesa) {
        echo "No mesas found in database\n";
        exit(1);
    }
    
    echo "Using mesa: " . $mesa['numero'] . " (ID: " . $mesa['id'] . ")\n";
    
    // Get a test product
    $stmt = $db->prepare("SELECT id, nombre, precio FROM productos LIMIT 1");
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        echo "No productos found in database\n";
        exit(1);
    }
    
    echo "Using producto: " . $producto['nombre'] . " (ID: " . $producto['id'] . ", Price: $" . $producto['precio'] . ")\n";
    
    // Create a JWT token for the test user
    $authController = new AuthController($db);
    
    // Get user with role information
    $stmt = $db->prepare("
        SELECT u.id, u.nombre, u.email, r.nombre as rol, u.rol_id 
        FROM usuarios u 
        LEFT JOIN roles r ON u.rol_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $userWithRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userWithRole) {
        echo "Could not get user with role information\n";
        exit(1);
    }
    
    // Use reflection to access the private generateToken method
    $reflection = new ReflectionClass($authController);
    $generateTokenMethod = $reflection->getMethod('generateToken');
    $generateTokenMethod->setAccessible(true);
    
    $token = $generateTokenMethod->invoke($authController, $userWithRole);
    echo "JWT token created\n";
    
    // Set the Authorization header for the test
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    
    // Test order data
    $orderData = [
        'mesa_id' => $mesa['id'],
        'items' => [
            [
                'producto_id' => $producto['id'],
                'cantidad' => 2,
                'notas' => 'Test order item'
            ]
        ],
        'notas' => 'Test order from API test'
    ];
    
    echo "\n=== Testing Order Creation ===\n";
    echo "Order data: " . json_encode($orderData, JSON_PRETTY_PRINT) . "\n";
    
    $pedidoController = new PedidoController($db);
    
    // Capture output
    ob_start();
    $pedidoController->store($orderData);
    $output = ob_get_clean();
    
    echo "Response: " . $output . "\n";
    
    echo "\nOrder creation test completed!\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    echo 'Stack trace: ' . $e->getTraceAsString() . "\n";
}