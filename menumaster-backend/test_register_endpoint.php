<?php
// Test del endpoint de registro para debuggear el error 400

// Configuración
$baseUrl = "http://localhost/MenuMaster/menumaster-backend/public";
$registerUrl = "$baseUrl/api/auth/register";

// Función para hacer peticiones HTTP
function makeRequest($url, $data, $method = 'POST') {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

echo "=== TEST DEL ENDPOINT DE REGISTRO ===\n\n";

// Test 1: Datos válidos con contraseña fuerte
echo "1. Probando con datos válidos y contraseña fuerte...\n";
$testData1 = [
    'nombre' => 'Usuario Test',
    'email' => 'test' . time() . '@example.com', // Email único
    'password' => 'Password123!', // Contraseña que cumple todos los requisitos
    'rol' => 'administrador'
];

$result1 = makeRequest($registerUrl, $testData1);
echo "HTTP Code: " . $result1['http_code'] . "\n";
echo "Response: " . $result1['response'] . "\n";
if ($result1['error']) {
    echo "cURL Error: " . $result1['error'] . "\n";
}
echo "\n";

// Test 2: Contraseña débil (sin mayúsculas)
echo "2. Probando con contraseña débil (sin mayúsculas)...\n";
$testData2 = [
    'nombre' => 'Usuario Test 2',
    'email' => 'test2' . time() . '@example.com',
    'password' => 'password123', // Sin mayúsculas
    'rol' => 'administrador'
];

$result2 = makeRequest($registerUrl, $testData2);
echo "HTTP Code: " . $result2['http_code'] . "\n";
echo "Response: " . $result2['response'] . "\n\n";

// Test 3: Contraseña muy corta
echo "3. Probando con contraseña muy corta...\n";
$testData3 = [
    'nombre' => 'Usuario Test 3',
    'email' => 'test3' . time() . '@example.com',
    'password' => '123', // Muy corta
    'rol' => 'administrador'
];

$result3 = makeRequest($registerUrl, $testData3);
echo "HTTP Code: " . $result3['http_code'] . "\n";
echo "Response: " . $result3['response'] . "\n\n";

// Test 4: Datos faltantes
echo "4. Probando con datos faltantes...\n";
$testData4 = [
    'nombre' => 'Usuario Test 4',
    // Falta email y password
    'rol' => 'administrador'
];

$result4 = makeRequest($registerUrl, $testData4);
echo "HTTP Code: " . $result4['http_code'] . "\n";
echo "Response: " . $result4['response'] . "\n\n";

// Test 5: Email inválido
echo "5. Probando con email inválido...\n";
$testData5 = [
    'nombre' => 'Usuario Test 5',
    'email' => 'email-invalido', // Email sin formato válido
    'password' => 'Password123!',
    'rol' => 'administrador'
];

$result5 = makeRequest($registerUrl, $testData5);
echo "HTTP Code: " . $result5['http_code'] . "\n";
echo "Response: " . $result5['response'] . "\n\n";

echo "=== ANÁLISIS DE REQUISITOS DE CONTRASEÑA ===\n";
echo "Según el código del AuthController, la contraseña debe:\n";
echo "- Tener al menos 8 caracteres\n";
echo "- Incluir al menos una letra mayúscula (A-Z)\n";
echo "- Incluir al menos una letra minúscula (a-z)\n";
echo "- Incluir al menos un número (0-9)\n\n";

echo "Ejemplo de contraseña válida: 'Password123'\n";
echo "Ejemplo de contraseña válida: 'MiClave456'\n";
echo "Ejemplo de contraseña válida: 'Admin2024'\n";