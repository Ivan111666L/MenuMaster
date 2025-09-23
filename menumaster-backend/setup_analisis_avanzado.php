<?php
/**
 * Script para configurar el sistema de análisis avanzado
 */

// Definir la ruta base
define('BASE_PATH', __DIR__ . '/menumaster-backend');

// Cargar configuración
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/conexionDb.php';

echo "=== CONFIGURACIÓN DEL SISTEMA DE ANÁLISIS AVANZADO ===\n\n";

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Ejecutar el SQL para ampliar la estructura
    echo "Aplicando cambios en la estructura de la base de datos...\n";
    $sql = file_get_contents(BASE_PATH . '/database/ampliar_analisis.sql');
    $db->exec($sql);
    
    echo "Estructura de base de datos actualizada correctamente.\n";
    
    echo "\n=== CONFIGURACIÓN COMPLETADA CON ÉXITO ===\n";
    echo "El sistema de análisis avanzado está listo para usar.\n";
    
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>