<?php
require_once 'vendor/autoload.php';
require_once 'App/Config/ConexionDb.php';
require_once 'App/Controllers/ProductoController.php';
require_once 'App/Controllers/CategoriaController.php';
require_once 'App/config/Config.php';

use App\Config\ConexionDb;
use App\Config\Config;
use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;
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

    // Generate a valid JWT token using backend config and set header
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

    // Test Product Controllers
    echo "\n=== Testing Products Module ===\n";
    
    $productoController = new ProductoController($db);
    $categoriaController = new CategoriaController($db);

    // Test 1: Get all categories
    echo "1. Testing categories index()...\n";
    try {
        ob_start();
        $categoriaController->index();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Categories retrieved successfully\n";
            if (isset($response['data']) && is_array($response['data'])) {
                echo "   - Found " . count($response['data']) . " categories\n";
                foreach (array_slice($response['data'], 0, 3) as $category) {
                    echo "   - {$category['nombre']}: {$category['descripcion']}\n";
                }
                // Use first category as test category
                if (!empty($response['data'])) {
                    $first = $response['data'][0];
                    $testCategoryId = $first['id'] ?? 1;
                    echo "   - Using category ID: " . $testCategoryId . "\n";
                }
            }
        } else {
            echo "❌ Failed to get categories\n";
            echo "Response: " . $output . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing categories index: " . $e->getMessage() . "\n";
    }

    // Test 2: Get all products
    echo "\n2. Testing products index()...\n";
    try {
        ob_start();
        $productoController->index();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Products retrieved successfully\n";
            if (isset($response['data']) && is_array($response['data'])) {
                echo "   - Found " . count($response['data']) . " products\n";
                foreach (array_slice($response['data'], 0, 3) as $product) {
                    echo "   - {$product['nombre']}: $" . $product['precio'] . " ({$product['categoria_nombre']})\n";
                }
            }
        } else {
            echo "❌ Failed to get products\n";
            echo "Response: " . $output . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing products index: " . $e->getMessage() . "\n";
    }

    // Test 3: Ensure we have a category ID from the index; fallback to 1
    if (!isset($testCategoryId)) {
        $testCategoryId = 1;
        echo "\n3. No category created; using fallback ID 1\n";
    } else {
        echo "\n3. Category ready for product creation (ID: $testCategoryId)\n";
    }

    // Test 4: Create a test product
    echo "\n4. Testing product creation...\n";
    try {
        $productData = [
            'nombre' => 'Test Product ' . time(),
            'descripcion' => 'Test product for module testing',
            'precio' => 15.99,
            'categoria_id' => $testCategoryId,
            'tiempo_preparacion_min' => 15
        ];

        // Simulate JSON input stream for php://input
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', 'TestInputWrapper');
        TestInputWrapper::$data = json_encode($productData);

        ob_start();
        $productoController->store();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Test product created successfully\n";
            echo "   - Product ID: " . ($response['data']['id'] ?? 'N/A') . "\n";
            $testProductId = $response['data']['id'] ?? null;
        } else {
            echo "❌ Failed to create test product\n";
            echo "Response: " . $output . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing product creation: " . $e->getMessage() . "\n";
    }

    // Test 5: Update product if created
    if (isset($testProductId)) {
        echo "\n5. Testing product update...\n";
        try {
            $updateData = [
                'nombre' => 'Updated Test Product ' . time(),
                'descripcion' => 'Updated test product description',
                'precio' => 19.99,
                'categoria_id' => $testCategoryId,
                'tiempo_preparacion_min' => 20
            ];

            $_SERVER['CONTENT_TYPE'] = 'application/json';
            $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
            stream_wrapper_unregister('php');
            stream_wrapper_register('php', 'TestInputWrapper');
            TestInputWrapper::$data = json_encode($updateData);

            ob_start();
            $productoController->update($testProductId);
            $output = ob_get_clean();
            
            $response = json_decode($output, true);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Product updated successfully\n";
            } else {
                echo "❌ Failed to update product\n";
                echo "Response: " . $output . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Error testing product update: " . $e->getMessage() . "\n";
        }
    }

    // Test 6: Get product by ID
    if (isset($testProductId)) {
        echo "\n6. Testing product show()...\n";
        try {
            ob_start();
            $productoController->show($testProductId);
            $output = ob_get_clean();
            
            $response = json_decode($output, true);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Product retrieved by ID successfully\n";
                if (isset($response['data'])) {
                    echo "   - Product: {$response['data']['nombre']} - $" . $response['data']['precio'] . "\n";
                }
            } else {
                echo "❌ Failed to get product by ID\n";
                echo "Response: " . $output . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Error testing product show: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Products Module Testing Complete ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Helper class to simulate php://input for controllers that read JSON
class TestInputWrapper {
    public static $data = '';
    private $position = 0;

    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->position = 0;
        return true;
    }

    public function stream_read($count) {
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof() {
        return $this->position >= strlen(self::$data);
    }

    public function stream_stat() {
        return [];
    }
}