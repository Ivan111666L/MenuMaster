<?php
require_once 'vendor/autoload.php';
require_once 'App/Config/ConexionDb.php';
require_once 'App/Controllers/IngredienteController.php';
require_once 'App/Controllers/MovimientoInventarioController.php';
require_once 'App/config/Config.php';

use App\Config\ConexionDb;
use App\Config\Config;
use App\Controllers\IngredienteController;
use App\Controllers\MovimientoInventarioController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Database connection
    $db = ConexionDb::getConnection();
    echo "✅ Database connected successfully\n";

    // Get admin user for JWT token
    $stmt = $db->prepare("SELECT u.*, r.nombre as role_name, r.id as role_id FROM usuarios u 
                         LEFT JOIN roles r ON u.rol_id = r.id 
                         WHERE u.email = 'admin@menumaster.com' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Admin user not found");
    }

    echo "✅ Using admin user: " . $user['nombre'] . " (ID: " . $user['id'] . ")\n";

    // Generate a valid JWT token using backend config
    $jwtConfig = Config::getJwtConfig();
    $issuedAt = time();
    $expirationTime = $issuedAt + ($jwtConfig['expiration'] ?? (24 * 60 * 60));
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'iss' => 'MenuMaster',
        'aud' => 'MenuMaster',
        'data' => [
            'id' => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol_id' => (int)($user['rol_id'] ?? $user['role_id'] ?? 1)
        ]
    ];
    $jwt = JWT::encode($payload, $jwtConfig['secret'], $jwtConfig['algorithm']);
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $jwt;
    echo "✅ JWT token generado y aplicado al header de autorización\n";

    // Test Inventory Controllers
    echo "\n=== Testing Inventory Module ===\n";
    
    $ingredienteController = new IngredienteController($db);
    $movimientoController = new MovimientoInventarioController($db);

    // Test 1: Get all ingredients
    echo "1. Testing index() method...\n";
    try {
        ob_start();
        $ingredienteController->index();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Ingredients retrieved successfully\n";
            if (isset($response['data']) && is_array($response['data'])) {
                echo "   - Found " . count($response['data']) . " ingredients\n";
                foreach (array_slice($response['data'], 0, 3) as $ingredient) {
                    echo "   - {$ingredient['nombre']}: {$ingredient['cantidad_actual']} {$ingredient['unidad_medida']}\n";
                }
            }
        } else {
            echo "❌ Failed to get ingredients\n";
            echo "Response: " . $output . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing index: " . $e->getMessage() . "\n";
    }

    // Test 2: Create a test ingredient
    echo "\n2. Testing store() method...\n";
    try {
        $nuevoIngredienteData = [
            'nombre' => 'Test Ingredient ' . time(),
            'descripcion' => 'Test ingredient for inventory testing',
            'stock_actual' => 100,
            'stock_minimo' => 10,
            'unidad_medida' => 'kg',
            'precio_compra' => 5.50
        ];

        ob_start();
        $ingredienteController->store($nuevoIngredienteData);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Test ingredient created successfully\n";
            echo "   - Ingredient ID: " . ($response['data']['id'] ?? 'N/A') . "\n";
            $testIngredientId = $response['data']['id'] ?? null;
        } else {
            echo "❌ Failed to create test ingredient\n";
            echo "Response: " . $output . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing store: " . $e->getMessage() . "\n";
    }

    // Test 3: Update ingredient stock using MovimientoInventarioController
    if (isset($testIngredientId)) {
        echo "\n3. Testing movement creation...\n";
        try {
            $movementData = [
                'ingrediente_id' => (int)$testIngredientId,
                'tipo' => 'entrada',
                'cantidad' => 50,
                'motivo' => 'Test stock update'
            ];

            ob_start();
            $movimientoController->store($movementData);
            $output = ob_get_clean();
            
            $response = json_decode($output, true);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Stock movement created successfully\n";
            } else {
                echo "❌ Failed to create stock movement\n";
                echo "Response: " . $output . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Error testing movement creation: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Inventory Module Testing Complete ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}