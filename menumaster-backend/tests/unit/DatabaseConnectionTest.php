<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\ConexionDb;
use App\Middleware\AuthMiddleware;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Probando conexión a base de datos...\n";
try {
    $db = ConexionDb::getConnection();
    echo "✅ Conexión exitosa\n";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

echo "2. Probando AuthMiddleware...\n";
try {
    $authMiddleware = new AuthMiddleware($db);
    echo "✅ AuthMiddleware creado\n";
} catch (Exception $e) {
    echo "❌ Error creando AuthMiddleware: " . $e->getMessage() . "\n";
    exit(1);
}

echo "3. Probando consulta de permisos directa...\n";
try {
    $stmt = $db->prepare("
        SELECT id, nombre, descripcion, modulo, accion, estado_id, created_at
        FROM permisos 
        WHERE estado_id = 1
        ORDER BY modulo, nombre
        LIMIT 5
    ");
    
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Consulta exitosa. Permisos encontrados: " . count($permisos) . "\n";
    
    if (count($permisos) > 0) {
        echo "Primer permiso: " . $permisos[0]['nombre'] . " (módulo: " . $permisos[0]['modulo'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en consulta: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Test completado\n";
