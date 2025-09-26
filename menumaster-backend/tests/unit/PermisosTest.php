<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\ConexionDb;
use App\Middleware\AuthMiddleware;
use App\Controllers\PermisosController;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST PERMISOS CONTROLLER ===\n";

try {
    echo "1. Conectando a base de datos...\n";
    $db = ConexionDb::getConnection();
    echo "✅ Conexión exitosa\n";

    echo "2. Creando AuthMiddleware...\n";
    $authMiddleware = new AuthMiddleware();
    echo "✅ AuthMiddleware creado\n";

    echo "3. Creando PermisosController...\n";
    $controller = new PermisosController();
    echo "✅ PermisosController creado\n";

    echo "4. Simulando token de autorización...\n";
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTg2OTIxOTcsImV4cCI6MTc1ODY5NTc5NywiaXNzIjoiTWVudU1hc3RlciIsImRhdGEiOnsiaWQiOjI4LCJlbWFpbCI6InRlc3RAbWVudW1hc3Rlci5jb20iLCJyb2wiOiJtZXNlcm8iLCJyb2xfaWQiOjJ9fQ.4RPPbO5ZVyUKdqDj6AfTPDPLyl9ITm9uE9ZBeZmzdMo';
    echo "✅ Token simulado\n";

    echo "5. Ejecutando getPermisos()...\n";
    ob_start();
    $controller->getPermisos();
    $output = ob_get_clean();
    
    echo "✅ Método ejecutado sin errores\n";
    echo "Salida del controlador:\n";
    echo $output . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== FIN TEST ===\n";