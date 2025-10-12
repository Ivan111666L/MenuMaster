<?php
// debug_facturacion_frontend.php
// Simula el flujo de facturación que realiza el frontend para verificar que no hay errores 500.

require_once __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno si existen
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Throwable $e) {
    // Ignorar si .env no existe
}

function curlJson($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $baseHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($baseHeaders, $headers));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    return [$httpCode, $response, $curlError];
}

$baseApi = 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api';

echo "=== DEBUG: Simulación de facturación desde frontend ===\n";

// 1) Login para obtener token
echo "\n1) Iniciando sesión...\n";
$loginPayload = [ 'email' => 'admin@menumaster.com', 'password' => 'admin123' ];
[$code, $body, $err] = curlJson("{$baseApi}/auth/login", 'POST', $loginPayload);
echo "Login HTTP: {$code}\n";
echo "Login Body: {$body}\n";
if ($err) echo "Login CURL Error: {$err}\n";
if ($code !== 200) {
    echo "ERROR: Falló el login, no se puede continuar.\n";
    exit(1);
}
$loginJson = json_decode($body, true);
if (!$loginJson || !isset($loginJson['token'])) {
    echo "ERROR: No se recibió token en el login.\n";
    exit(1);
}
$token = $loginJson['token'];
echo "Token recibido (parcial): " . substr($token, 0, 40) . "...\n";

// 2) Obtener pedidos listos para facturar (como hace el frontend)
echo "\n2) Consultando pedidos para facturar...\n";
$estados = urlencode('servido,pendiente,en preparacion');
[$code, $body, $err] = curlJson("{$baseApi}/pedidos?estado={$estados}", 'GET', null, ["Authorization: Bearer {$token}"]);
echo "Pedidos HTTP: {$code}\n";
echo "Pedidos Body: {$body}\n";
if ($err) echo "Pedidos CURL Error: {$err}\n";
if ($code !== 200) {
    echo "ERROR: No se pudieron obtener pedidos.\n";
    exit(1);
}
$pedidosJson = json_decode($body, true);
$lista = [];
if (isset($pedidosJson['data']) && is_array($pedidosJson['data'])) {
    $lista = $pedidosJson['data'];
} elseif (is_array($pedidosJson)) {
    $lista = $pedidosJson;
}
if (empty($lista)) {
    echo "AVISO: No hay pedidos disponibles para facturar. Cree un pedido y vuelva a intentar.\n";
    exit(0);
}

$pedido = $lista[0];
$pedidoId = $pedido['id'] ?? null;
if (!$pedidoId) {
    echo "ERROR: El primer elemento no tiene 'id' válido.\n";
    exit(1);
}
echo "Seleccionado Pedido #{$pedidoId} (Mesa: " . ($pedido['mesa_numero'] ?? 'N/A') . ")\n";

// 3) Facturar el pedido como hace el frontend
echo "\n3) Facturando el pedido...\n";
$pagoPayload = [
    'metodo_pago' => 'efectivo',
    'dividir' => false,
    'personas' => 1,
];
[$code, $body, $err] = curlJson("{$baseApi}/pedidos/{$pedidoId}/facturar", 'POST', $pagoPayload, ["Authorization: Bearer {$token}"]);
echo "Facturar HTTP: {$code}\n";
echo "Facturar Body: {$body}\n";
if ($err) echo "Facturar CURL Error: {$err}\n";

if ($code >= 500) {
    echo "ERROR: El endpoint devolvió un error del servidor (500).\n";
    exit(1);
}

echo "\nListo: Facturación simulada sin errores 500.\n";
echo "=== FIN DEBUG ===\n";