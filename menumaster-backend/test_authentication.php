<?php
// Configurar el entorno para evitar warnings de headers
ob_start();

// Cargar dependencias
require_once __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Simular el entorno de API
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

// Simular datos de login
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

// Simular el input JSON
$_POST = [];
file_put_contents('php://input', json_encode($loginData));

// Capturar la salida
ob_start();

try {
    // Requerir el controlador
    require_once __DIR__ . '/App/Controllers/AuthController.php';
    
    // Crear instancia del controlador
    $authController = new App\Controllers\AuthController();
    
    // Intentar login
    $authController->login();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Obtener la salida
$output = ob_get_clean();

// Limpiar el buffer inicial
ob_end_clean();

// Mostrar resultado
echo "=== RESULTADO DE PRUEBA DE AUTENTICACIÓN ===\n";
echo $output;
echo "\n=== FIN DE PRUEBA ===\n";