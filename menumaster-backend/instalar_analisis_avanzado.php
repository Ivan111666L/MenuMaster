<?php
// Definir la ruta base
define('BASE_PATH', __DIR__);

// Cargar configuración
require_once BASE_PATH . '/App/config/config.php';

echo "=== INSTALACIÓN DEL SISTEMA DE ANÁLISIS AVANZADO ===\n\n";

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Conexión a la base de datos establecida correctamente.\n\n";
    
    // Crear tablas necesarias para el análisis avanzado
    echo "Creando estructura para análisis avanzado...\n";
    
    // 1. Crear tabla de cuadre diario si no existe
    $db->exec("
        CREATE TABLE IF NOT EXISTS cuadre_diario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATE NOT NULL,
            total_ventas DECIMAL(10,2) DEFAULT 0,
            total_costos DECIMAL(10,2) DEFAULT 0,
            total_compras_proveedores DECIMAL(10,2) DEFAULT 0,
            notas TEXT,
            creado_por INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (fecha)
        ) ENGINE=InnoDB;
    ");
    
    // 2. Modificar tabla de productos_ingredientes para incluir costo
    $db->exec("
        ALTER TABLE productos_ingredientes 
        ADD COLUMN IF NOT EXISTS costo_unitario DECIMAL(10,2) DEFAULT 0
        AFTER cantidad_requerida;
    ");
    
    // 3. Modificar tabla de detalles_pedido para incluir costo total
    $db->exec("
        ALTER TABLE detalles_pedido 
        ADD COLUMN IF NOT EXISTS costo_total DECIMAL(10,2) DEFAULT 0
        AFTER subtotal;
    ");
    
    // 4. Modificar tabla de historial_detalles_pedido para incluir costo total
    $db->exec("
        ALTER TABLE historial_detalles_pedido 
        ADD COLUMN IF NOT EXISTS costo_total DECIMAL(10,2) DEFAULT 0
        AFTER subtotal;
    ");
    
    // 5. Crear tabla de proveedores si no existe
    $db->exec("
        CREATE TABLE IF NOT EXISTS proveedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            telefono VARCHAR(20),
            email VARCHAR(100),
            direccion TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ");
    
    // 6. Modificar tabla de ingredientes para incluir proveedor_id
    $db->exec("
        ALTER TABLE ingredientes 
        ADD COLUMN IF NOT EXISTS proveedor_id INT NULL
        AFTER precio_unitario;
    ");
    
    // 7. Agregar clave foránea si no existe
    $result = $db->query("
        SELECT COUNT(*) 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '{$config['db']['name']}' 
        AND TABLE_NAME = 'ingredientes' 
        AND COLUMN_NAME = 'proveedor_id' 
        AND REFERENCED_TABLE_NAME = 'proveedores'
    ");
    
    if ($result->fetchColumn() == 0) {
        $db->exec("
            ALTER TABLE ingredientes
            ADD CONSTRAINT fk_ingrediente_proveedor
            FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
            ON DELETE SET NULL;
        ");
    }
    
    echo "✓ Estructura de base de datos para análisis avanzado creada correctamente.\n\n";
    
    // Verificar si hay datos de ejemplo para insertar
    echo "Verificando datos de ejemplo...\n";
    
    // Insertar proveedor de ejemplo si no existe ninguno
    $result = $db->query("SELECT COUNT(*) FROM proveedores");
    if ($result->fetchColumn() == 0) {
        $db->exec("
            INSERT INTO proveedores (nombre, telefono, email, direccion)
            VALUES 
            ('Distribuidora de Carnes El Buen Sabor', '3101234567', 'contacto@buensabor.com', 'Calle 123 #45-67'),
            ('Frutas y Verduras Frescas', '3157654321', 'ventas@frutasfrescas.com', 'Carrera 78 #90-12'),
            ('Lácteos La Vaca Feliz', '3209876543', 'pedidos@vacafeliz.com', 'Avenida 45 #23-56');
        ");
        echo "✓ Proveedores de ejemplo insertados correctamente.\n";
    } else {
        echo "✓ Ya existen proveedores en la base de datos.\n";
    }
    
    echo "\n=== INSTALACIÓN COMPLETADA CON ÉXITO ===\n\n";
    echo "El sistema de análisis avanzado ha sido instalado correctamente.\n";
    echo "Nuevas funcionalidades disponibles:\n";
    echo "1. Descuento automático de inventario al facturar\n";
    echo "2. Registro de costos de ingredientes por producto\n";
    echo "3. Cuadre diario con rentabilidad\n";
    echo "4. Gestión de proveedores con ingredientes\n";
    echo "5. Análisis de rentabilidad por producto\n\n";
    
    echo "Nuevas rutas API disponibles:\n";
    echo "- /cuadre_diario - Gestión de cuadres diarios\n";
    echo "- /cuadre_diario/rentabilidad-productos - Análisis de rentabilidad por producto\n";
    echo "- /cuadre_diario/resumen-ventas - Resumen de ventas diarias\n";
    echo "- /cuadre_diario/inventario-proveedores - Inventario con información de proveedores\n\n";
    
    echo "Acceda al nuevo módulo de análisis en el frontend:\n";
    echo "- /analisis - Resumen de ventas\n";
    echo "- /analisis/rentabilidad - Rentabilidad de productos\n";
    echo "- /analisis/cuadre-diario - Cuadre diario\n";
    echo "- /analisis/inventario-proveedores - Inventario y proveedores\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>