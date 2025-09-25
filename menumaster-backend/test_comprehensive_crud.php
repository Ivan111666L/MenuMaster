<?php
// Comprehensive CRUD operations test
echo "=== COMPREHENSIVE CRUD OPERATIONS TEST ===\n";

// Base URL for API
$baseUrl = 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api';

// Test credentials
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

// Function to make API calls
function makeApiCall($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => json_decode($response, true)];
}

// 1. Test Authentication
echo "\n1. TESTING AUTHENTICATION\n";
echo "========================\n";
$result = makeApiCall("$baseUrl/auth/login", 'POST', $loginData);
if ($result['code'] === 200 && isset($result['response']['token'])) {
    $token = $result['response']['token'];
    echo "✅ Login successful\n";
} else {
    echo "❌ Login failed\n";
    exit(1);
}

// 2. Test Products CRUD
echo "\n2. TESTING PRODUCTS CRUD\n";
echo "========================\n";

// CREATE Product
$productData = [
    'nombre' => 'CRUD Test Product ' . time(),
    'descripcion' => 'Test product for CRUD operations',
    'precio' => 15.99,
    'categoria_id' => 2,
    'tiempo_preparacion_min' => 10
];

$result = makeApiCall("$baseUrl/productos", 'POST', $productData, $token);
if ($result['code'] === 201) {
    $productId = $result['response']['data']['id'];
    echo "✅ Product created with ID: $productId\n";
} else {
    echo "❌ Product creation failed: " . json_encode($result) . "\n";
    $productId = null;
}

// READ Products (List all)
$result = makeApiCall("$baseUrl/productos", 'GET', null, $token);
if ($result['code'] === 200) {
    $productCount = count($result['response']['data'] ?? []);
    echo "✅ Products list retrieved ($productCount products)\n";
} else {
    echo "❌ Products list failed\n";
}

// READ Product (Single)
if ($productId) {
    $result = makeApiCall("$baseUrl/productos/$productId", 'GET', null, $token);
    if ($result['code'] === 200) {
        echo "✅ Single product retrieved\n";
    } else {
        echo "❌ Single product retrieval failed\n";
    }
}

// UPDATE Product
if ($productId) {
    $updateData = [
        'nombre' => 'Updated CRUD Test Product',
        'precio' => 19.99
    ];
    $result = makeApiCall("$baseUrl/productos/$productId", 'PUT', $updateData, $token);
    if ($result['code'] === 200) {
        echo "✅ Product updated\n";
    } else {
        echo "❌ Product update failed\n";
    }
}

// DELETE Product
if ($productId) {
    $result = makeApiCall("$baseUrl/productos/$productId", 'DELETE', null, $token);
    if ($result['code'] === 200) {
        echo "✅ Product deleted\n";
    } else {
        echo "❌ Product deletion failed\n";
    }
}

// 3. Test Ingredients CRUD
echo "\n3. TESTING INGREDIENTS CRUD\n";
echo "===========================\n";

// CREATE Ingredient
$ingredientData = [
    'nombre' => 'CRUD Test Ingredient ' . time(),
    'descripcion' => 'Test ingredient for CRUD operations',
    'unidad_medida' => 'kg'
];

$result = makeApiCall("$baseUrl/ingredientes", 'POST', $ingredientData, $token);
if ($result['code'] === 201) {
    $ingredientId = $result['response']['data']['id'] ?? null;
    echo "✅ Ingredient created with ID: $ingredientId\n";
} else {
    echo "❌ Ingredient creation failed: " . json_encode($result) . "\n";
    $ingredientId = null;
}

// READ Ingredients
$result = makeApiCall("$baseUrl/ingredientes", 'GET', null, $token);
if ($result['code'] === 200) {
    $ingredientCount = count($result['response']['data'] ?? []);
    echo "✅ Ingredients list retrieved ($ingredientCount ingredients)\n";
} else {
    echo "❌ Ingredients list failed\n";
}

// UPDATE Ingredient
if ($ingredientId) {
    $updateData = [
        'nombre' => 'Updated CRUD Test Ingredient',
        'descripcion' => 'Updated description'
    ];
    $result = makeApiCall("$baseUrl/ingredientes/$ingredientId", 'PUT', $updateData, $token);
    if ($result['code'] === 200) {
        echo "✅ Ingredient updated\n";
    } else {
        echo "❌ Ingredient update failed\n";
    }
}

// DELETE Ingredient
if ($ingredientId) {
    $result = makeApiCall("$baseUrl/ingredientes/$ingredientId", 'DELETE', null, $token);
    if ($result['code'] === 200) {
        echo "✅ Ingredient deleted\n";
    } else {
        echo "❌ Ingredient deletion failed\n";
    }
}

// 4. Test Categories (Read-only for now)
echo "\n4. TESTING CATEGORIES\n";
echo "====================\n";

$result = makeApiCall("$baseUrl/categorias", 'GET', null, $token);
if ($result['code'] === 200) {
    $categoryCount = count($result['response']['data'] ?? []);
    echo "✅ Categories list retrieved ($categoryCount categories)\n";
} else {
    echo "❌ Categories list failed\n";
}

echo "\n=== CRUD TESTING COMPLETED ===\n";