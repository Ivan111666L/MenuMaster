<?php
/**
 * Script para configurar el sistema de análisis avanzado
 */

// Definir la ruta base
define('BASE_PATH', __DIR__);

// Cargar configuración
require_once BASE_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

echo "=== CONFIGURACIÓN DEL SISTEMA DE ANÁLISIS AVANZADO ===\n\n";

try {
    // Obtener configuración de la base de datos desde variables de entorno
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
    $dbName = $_ENV['DB_NAME'] ?? 'menu_master';
    $dbUser = $_ENV['DB_USER'] ?? 'root';
    $dbPass = $_ENV['DB_PASS'] ?? '';
    
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser, 
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "Conexión a la base de datos establecida correctamente.\n";
    
    // Verificar si el archivo SQL existe
    $sqlFile = BASE_PATH . '/database/ampliar_analisis.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("El archivo ampliar_analisis.sql no existe en: {$sqlFile}");
    }
    
    // Ejecutar el SQL para ampliar la estructura
    echo "Aplicando cambios en la estructura de la base de datos...\n";
    $sql = file_get_contents($sqlFile);
    
    // Dividir el SQL en declaraciones individuales
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "✅ Ejecutado: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                // Si es un error de tabla ya existente, continuar
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "⚠️  Ya existe: " . substr($statement, 0, 50) . "...\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "\nEstructura de base de datos actualizada correctamente.\n";
    
    echo "\n=== CONFIGURACIÓN COMPLETADA CON ÉXITO ===\n";
    echo "El sistema de análisis avanzado está listo para usar.\n";
    
} catch (PDOException $e) {
    echo "❌ Error en la base de datos: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>