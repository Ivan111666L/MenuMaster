<?php
// Definir la ruta base
define('BASE_PATH', __DIR__);

// Cargar configuración
require_once BASE_PATH . '/App/config/config.php';

echo "=== INSTALACIÓN DEL SISTEMA DE COMBOS Y MEJORAS ===\n\n";

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Conexión a la base de datos establecida correctamente.\n\n";
    
    // Ejecutar el script SQL
    echo "Aplicando cambios a la base de datos...\n";
    $sql = file_get_contents(BASE_PATH . '/database/combos.sql');
    $db->exec($sql);
    
    echo "✓ Estructura de base de datos para combos creada correctamente.\n\n";
    
    // Insertar datos de ejemplo
    echo "Insertando datos de ejemplo...\n";
    
    // Crear algunos combos de ejemplo
    $db->exec("
        INSERT INTO `combos` (`nombre`, `descripcion`, `precio`, `descuento`, `imagen_url`, `destacado`) VALUES
        ('Combo Hamburguesa', 'Hamburguesa con papas fritas y refresco', 25000.00, 3000.00, 'combo_hamburguesa.jpg', 1),
        ('Combo Pollo', 'Pollo frito con ensalada y refresco', 28000.00, 4000.00, 'combo_pollo.jpg', 1),
        ('Combo Familiar', 'Pizza mediana, 4 refrescos y postre', 45000.00, 8000.00, 'combo_familiar.jpg', 1);
    ");
    
    // Obtener IDs de algunos productos para los combos
    $stmt = $db->query("SELECT id, nombre FROM productos LIMIT 10");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($productos) >= 6) {
        // Asignar productos a los combos
        $db->exec("
            INSERT INTO `combo_elementos` (`combo_id`, `producto_id`, `cantidad`, `opcional`) VALUES
            (1, {$productos[0]['id']}, 1, 0), -- Producto principal
            (1, {$productos[1]['id']}, 1, 1), -- Acompañamiento opcional
            (1, {$productos[2]['id']}, 1, 1), -- Bebida opcional
            
            (2, {$productos[3]['id']}, 1, 0), -- Producto principal
            (2, {$productos[4]['id']}, 1, 1), -- Acompañamiento opcional
            (2, {$productos[2]['id']}, 1, 1), -- Bebida opcional
            
            (3, {$productos[5]['id']}, 1, 0), -- Producto principal
            (3, {$productos[1]['id']}, 2, 1), -- Acompañamiento opcional (2 unidades)
            (3, {$productos[2]['id']}, 4, 1)  -- Bebidas opcionales (4 unidades)
        ");
        
        echo "✓ Datos de ejemplo para combos insertados correctamente.\n";
    } else {
        echo "⚠ No hay suficientes productos para crear combos de ejemplo.\n";
    }
    
    // Actualizar menú del día con límites de stock
    $db->exec("
        UPDATE `menu_del_dia` 
        SET `stock_limite` = 20, `stock_actual` = 20 
        WHERE `stock_limite` IS NULL;
    ");
    
    echo "✓ Menú del día actualizado con límites de stock.\n\n";
    
    echo "=== INSTALACIÓN COMPLETADA CON ÉXITO ===\n\n";
    echo "El sistema de combos y mejoras ha sido instalado correctamente.\n";
    echo "Nuevas funcionalidades disponibles:\n";
    echo "1. Creación y gestión de combos de productos\n";
    echo "2. Posibilidad de cancelar elementos individuales del combo\n";
    echo "3. Límite de stock para productos en el menú del día\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>