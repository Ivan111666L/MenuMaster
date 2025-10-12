<?php
// test_mesas_pedidos_api_flow.php
// End-to-end API test: select available mesa, create pedido, verify mesa state changes to OCUPADA,
// then update pedido status to SERVIDO and verify mesa returns to DISPONIBLE.

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/App/config/Constantes.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/MenuMaster/menumaster-backend/public';

function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $httpHeaders = $headers ?: ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $parsed = null;
    if ($responseBody) {
        $parsed = json_decode($responseBody, true);
    }
    return [
        'code' => $httpCode,
        'body' => $responseBody,
        'data' => $parsed,
        'error' => $curlErr,
        'success' => $httpCode >= 200 && $httpCode < 300
    ];
}

function getArrayDataFromResponse($parsed) {
    // Normaliza diferentes formatos de respuesta: {data: [...] } o [...]
    if (is_array($parsed)) {
        if (isset($parsed['data']) && is_array($parsed['data'])) {
            return $parsed['data'];
        }
        // Si el propio parsed es un arreglo indexado
        $keys = array_keys($parsed);
        $isIndexed = $keys === range(0, count($parsed) - 1);
        if ($isIndexed) {
            return $parsed;
        }
    }
    return [];
}

function getAuthToken(): ?string {
    global $baseUrl;

    // Try admin login first
    $loginData = [
        'email' => 'admin@menumaster.com',
        'password' => 'admin123'
    ];
    $result = makeHttpRequest("$baseUrl/api/auth/login", 'POST', $loginData);
    if ($result['success'] && isset($result['data']['token'])) {
        echo "✅ Admin login successful\n";
        return $result['data']['token'];
    }

    // Fallback: try test user
    $loginData = [
        'email' => 'test@example.com',
        'password' => 'test123'
    ];
    $result = makeHttpRequest("$baseUrl/api/auth/login", 'POST', $loginData);
    if ($result['success'] && isset($result['data']['token'])) {
        echo "✅ Test user login successful\n";
        return $result['data']['token'];
    }

    // Register a new temp user if login fails
    $registerData = [
        'nombre' => 'API Flow Tester',
        'email' => 'apiflowtester_' . time() . '@example.com',
        'password' => 'TestPass123!',
        'rol_id' => 2,
        'estado_id' => 1
    ];
    $reg = makeHttpRequest("$baseUrl/api/auth/register", 'POST', $registerData);
    if ($reg['success']) {
        echo "✅ Registered temp user: {$registerData['email']}\n";
        $loginData = [
            'email' => $registerData['email'],
            'password' => $registerData['password']
        ];
        $login = makeHttpRequest("$baseUrl/api/auth/login", 'POST', $loginData);
        if ($login['success'] && isset($login['data']['token'])) {
            echo "✅ Temp user login successful\n";
            return $login['data']['token'];
        }
    }

    echo "❌ Failed to obtain auth token\n";
    if (!empty($reg['body'])) echo "Register response: {$reg['body']}\n";
    return null;
}

echo "=== Mesas & Pedidos API Flow Test ===\n\n";

$token = getAuthToken();
if (!$token) {
    exit(1);
}

$authHeader = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
];

// Step 1: Fetch available mesas
echo "1) Fetching available mesas...\n";
$mesasResp = makeHttpRequest("$baseUrl/api/mesas/disponibles", 'GET', null, $authHeader);
if (!$mesasResp['success']) {
    echo "❌ Failed to get available mesas (HTTP {$mesasResp['code']})\n";
    echo substr($mesasResp['body'], 0, 300) . "\n";
    exit(1);
}
$mesasDisponibles = getArrayDataFromResponse($mesasResp['data']);
if (empty($mesasDisponibles)) {
    echo "⚠️ No available mesas found. Attempting admin reset...\n";
    // Try admin token specifically for reset
    $adminToken = null;
    $adminLogin = makeHttpRequest("$baseUrl/api/auth/login", 'POST', ['email' => 'admin@menumaster.com', 'password' => 'admin123']);
    if ($adminLogin['success'] && isset($adminLogin['data']['token'])) {
        $adminToken = $adminLogin['data']['token'];
        $adminHeader = ['Content-Type: application/json', 'Authorization: Bearer ' . $adminToken];
        $resetResp = makeHttpRequest("$baseUrl/api/mesas/reset", 'POST', [], $adminHeader);
        if ($resetResp['success']) {
            echo "✅ Mesas reset to DISPONIBLE\n";
            $mesasResp = makeHttpRequest("$baseUrl/api/mesas/disponibles", 'GET', null, $authHeader);
            $mesasDisponibles = getArrayDataFromResponse($mesasResp['data']);
        } else {
            echo "❌ Failed to reset mesas\n";
        }

        // Si aún no hay mesas disponibles, crear una mediante API admin
        if (empty($mesasDisponibles)) {
            echo "⚠️ No available mesas after reset. Creating a new mesa via API...\n";
            $createPayload = [
                'numero' => rand(100, 999),
                'capacidad' => 4,
                'ubicacion' => 'Zona Test'
            ];
            $crearMesaResp = makeHttpRequest("$baseUrl/api/mesas", 'POST', $createPayload, $adminHeader);
            if ($crearMesaResp['success']) {
                $nuevaMesa = $crearMesaResp['data']['data'] ?? $crearMesaResp['data'] ?? null;
                $nuevoId = is_array($nuevaMesa) ? ($nuevaMesa['id'] ?? null) : null;
                echo "✅ Mesa creada correctamente" . ($nuevoId ? " (ID $nuevoId)" : "") . "\n";
                // Forzar estado disponible explícitamente por si el backend no lo establece
                if ($nuevoId) {
                    $updResp = makeHttpRequest("$baseUrl/api/mesas/" . (int)$nuevoId, 'PUT', ['estado_nombre' => 'disponible'], $adminHeader);
                    if ($updResp['success']) {
                        echo "✅ Mesa $nuevoId actualizada a DISPONIBLE\n";
                    } else {
                        echo "⚠️ No se pudo actualizar explícitamente la mesa a DISPONIBLE\n";
                    }
                }
                // Reintentar obtener mesas disponibles
                $mesasResp = makeHttpRequest("$baseUrl/api/mesas/disponibles", 'GET', null, $authHeader);
                $mesasDisponibles = getArrayDataFromResponse($mesasResp['data']);
            } else {
                echo "❌ No fue posible crear una mesa de prueba\n";
            }
        }
    }
}

// Fallback adicional: si sigue vacío, obtener todas las mesas y filtrar en cliente
if (empty($mesasDisponibles)) {
    echo "ℹ️ Fallback: obteniendo todas las mesas para filtrar disponibles...\n";
    $todasResp = makeHttpRequest("$baseUrl/api/mesas?todas=true", 'GET', null, $authHeader);
    if ($todasResp['success']) {
        $todas = getArrayDataFromResponse($todasResp['data']);
        $mesasDisponibles = array_values(array_filter($todas, function($m) {
            $estado = strtolower($m['estado'] ?? $m['estado_nombre'] ?? '');
            $estadoId = isset($m['estado_id']) ? (int)$m['estado_id'] : null;
            return $estado === 'disponible' || $estadoId === \App\EstadosMesa::DISPONIBLE;
        }));
        // Si aún no hay, intentar poner la primera mesa en disponible vía admin
        if (empty($mesasDisponibles) && !empty($todas)) {
            echo "⚠️ No hay mesas disponibles. Intentando actualizar la primera mesa a DISPONIBLE...\n";
            $adminLogin = makeHttpRequest("$baseUrl/api/auth/login", 'POST', ['email' => 'admin@menumaster.com', 'password' => 'admin123']);
            if ($adminLogin['success'] && isset($adminLogin['data']['token'])) {
                $adminHeader = ['Content-Type: application/json', 'Authorization: Bearer ' . $adminLogin['data']['token']];
                $firstMesaId = (int)($todas[0]['id'] ?? 0);
                if ($firstMesaId) {
                    $updResp = makeHttpRequest("$baseUrl/api/mesas/" . $firstMesaId, 'PUT', ['estado_nombre' => 'disponible'], $adminHeader);
                    if ($updResp['success']) {
                        echo "✅ Mesa $firstMesaId actualizada a DISPONIBLE\n";
                        $mesasResp = makeHttpRequest("$baseUrl/api/mesas/disponibles", 'GET', null, $authHeader);
                        $mesasDisponibles = getArrayDataFromResponse($mesasResp['data']);
                    } else {
                        echo "❌ No se pudo actualizar la mesa $firstMesaId a DISPONIBLE\n";
                    }
                }
            }
        }
    } else {
        echo "❌ No se pudieron obtener todas las mesas (HTTP {$todasResp['code']})\n";
    }
}

if (empty($mesasDisponibles)) {
    // Intentar seleccionar la primera mesa de 'todas' ya actualizada a disponible
    $todasResp2 = makeHttpRequest("$baseUrl/api/mesas", 'GET', null, $authHeader);
    $todas2 = $todasResp2['success'] ? getArrayDataFromResponse($todasResp2['data']) : [];
    if (!empty($todas2)) {
        $mesaSeleccionada = $todas2[0];
        echo "ℹ️ Usando mesa de la lista completa: #" . ($mesaSeleccionada['numero'] ?? $mesaSeleccionada['id'] ?? '?') . "\n";
    } else {
        echo "❌ No available mesas to test with\n";
        exit(1);
    }
} else {
    $mesaSeleccionada = $mesasDisponibles[0];
}
echo "   Selected mesa #{$mesaSeleccionada['numero']} (ID {$mesaSeleccionada['id']})\n";

// Step 2: Fetch products to build order items
echo "2) Fetching products...\n";
$productosResp = makeHttpRequest("$baseUrl/api/productos", 'GET', null, $authHeader);
if (!$productosResp['success']) {
    echo "❌ Failed to get products (HTTP {$productosResp['code']})\n";
    echo substr($productosResp['body'], 0, 300) . "\n";
    exit(1);
}
$productos = $productosResp['data']['data'] ?? [];
if (empty($productos)) {
    echo "❌ No products found to create order\n";
    exit(1);
}
$producto = $productos[0];
echo "   Selected product: {$producto['nombre']} (ID {$producto['id']})\n";

// Step 3: Create order for selected mesa
echo "3) Creating order...\n";
$pedidoData = [
    'mesa_id' => (int)$mesaSeleccionada['id'],
    'items' => [
        [
            'producto_id' => (int)$producto['id'],
            'cantidad' => 1
        ]
    ],
    'notas' => 'API flow test order'
];
$crearResp = makeHttpRequest("$baseUrl/api/pedidos", 'POST', $pedidoData, $authHeader);
if (!$crearResp['success']) {
    echo "❌ Failed to create order (HTTP {$crearResp['code']})\n";
    echo substr($crearResp['body'], 0, 300) . "\n";
    exit(1);
}
$pedidoCreado = $crearResp['data']['data'] ?? null;
// Fallbacks por si la API devuelve formatos alternativos
if (!$pedidoCreado || !is_array($pedidoCreado) || !isset($pedidoCreado['id'])) {
    // Si la respuesta tiene success/data en el nivel raíz
    if (is_array($crearResp['data']) && isset($crearResp['data']['success']) && isset($crearResp['data']['data']) && is_array($crearResp['data']['data'])) {
        $pedidoCreado = $crearResp['data']['data'];
    } elseif (is_array($crearResp['data']) && isset($crearResp['data']['pedido'])) {
        $pedidoCreado = $crearResp['data']['pedido'];
    }
}
if (!$pedidoCreado || !isset($pedidoCreado['id'])) {
    echo "❌ Invalid order creation response\n";
    echo "   Raw body: " . substr(($crearResp['body'] ?? ''), 0, 500) . "\n";
    echo "   Parsed: " . json_encode($crearResp['data']) . "\n";
    exit(1);
}
echo "   Order created: ID {$pedidoCreado['id']} for mesa {$pedidoCreado['mesa_id']}\n";

// Step 4: Verify mesa state changed to OCUPADA
echo "4) Verifying mesa state is OCUPADA...\n";
$mesaShowResp = makeHttpRequest("$baseUrl/api/mesas/" . (int)$mesaSeleccionada['id'], 'GET', null, $authHeader);
if (!$mesaShowResp['success']) {
    echo "❌ Failed to fetch mesa after order\n";
    exit(1);
}
$mesaDet = $mesaShowResp['data']['data'] ?? [];
$estadoId = $mesaDet['estado_id'] ?? null;
if ((int)$estadoId === \App\EstadosMesa::OCUPADA) {
    echo "✅ Mesa state updated to OCUPADA (estado_id={$estadoId})\n";
} else {
    echo "❌ Mesa state not OCUPADA, got estado_id=" . var_export($estadoId, true) . "\n";
    exit(1);
}

// Step 5: Update order status to SERVIDO
echo "5) Updating order status to SERVIDO...\n";
$updateResp = makeHttpRequest("$baseUrl/api/pedidos/" . (int)$pedidoCreado['id'] . "/estado", 'PUT', ['estado_id' => \App\EstadosPedido::SERVIDO], $authHeader);
if (!$updateResp['success']) {
    echo "❌ Failed to update order status (HTTP {$updateResp['code']})\n";
    echo substr($updateResp['body'], 0, 300) . "\n";
    exit(1);
}
echo "   Order status updated to SERVIDO\n";

// Step 6: Verify mesa state returned to DISPONIBLE
echo "6) Verifying mesa state is DISPONIBLE...\n";
$mesaShowResp2 = makeHttpRequest("$baseUrl/api/mesas/" . (int)$mesaSeleccionada['id'], 'GET', null, $authHeader);
if (!$mesaShowResp2['success']) {
    echo "❌ Failed to fetch mesa after status update\n";
    exit(1);
}
$mesaDet2 = $mesaShowResp2['data']['data'] ?? [];
$estadoId2 = $mesaDet2['estado_id'] ?? null;
if ((int)$estadoId2 === \App\EstadosMesa::DISPONIBLE) {
    echo "✅ Mesa state returned to DISPONIBLE (estado_id={$estadoId2})\n";
} else {
    echo "❌ Mesa state not DISPONIBLE, got estado_id=" . var_export($estadoId2, true) . "\n";
    exit(1);
}

echo "\n🎉 Flow completed successfully!\n";