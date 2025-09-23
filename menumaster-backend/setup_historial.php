<?php
// Script para configurar las tablas de historial de pedidos
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
use app\config\ConexionDb;

echo "=== CONFIGURANDO TABLAS DE HISTORIAL DE PEDIDOS ===\n\n";

try {
    $db = ConexionDb::getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/database/historial_pedidos.sql');
    
    // Ejecutar las consultas SQL
    $db->exec($sql);
    
    echo "✅ Tablas de historial creadas correctamente\n";
    
    // Corregir el campo total en la tabla pedidos para que no sea VIRTUAL
    $db->exec("
        ALTER TABLE pedidos 
        MODIFY COLUMN total decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total calculado del pedido'
    ");
    
    echo "✅ Campo total en tabla pedidos corregido\n";
    
    // Crear un trigger para actualizar automáticamente el total del pedido
    $db->exec("
        DROP TRIGGER IF EXISTS actualizar_total_pedido;
        
        CREATE TRIGGER actualizar_total_pedido
        AFTER INSERT ON detalles_pedido
        FOR EACH ROW
        BEGIN
            UPDATE pedidos 
            SET total = (
                SELECT SUM(subtotal) 
                FROM detalles_pedido 
                WHERE pedido_id = NEW.pedido_id
            )
            WHERE id = NEW.pedido_id;
        END;
    ");
    
    echo "✅ Trigger para actualizar total creado\n";
    
    // Crear un trigger para actualizar el total cuando se elimina un detalle
    $db->exec("
        DROP TRIGGER IF EXISTS actualizar_total_pedido_delete;
        
        CREATE TRIGGER actualizar_total_pedido_delete
        AFTER DELETE ON detalles_pedido
        FOR EACH ROW
        BEGIN
            UPDATE pedidos 
            SET total = (
                SELECT COALESCE(SUM(subtotal), 0) 
                FROM detalles_pedido 
                WHERE pedido_id = OLD.pedido_id
            )
            WHERE id = OLD.pedido_id;
        END;
    ");
    
    echo "✅ Trigger para actualizar total al eliminar creado\n";
    
    echo "\n=== CONFIGURACIÓN COMPLETADA CON ÉXITO ===\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>