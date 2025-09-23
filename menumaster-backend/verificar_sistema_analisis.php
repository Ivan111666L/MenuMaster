<?php
// Definir la ruta base
define('BASE_PATH', __DIR__ . '/menumaster-backend');

// Cargar configuración
require_once BASE_PATH . '/App/config/config.php';

echo "=== VERIFICACIÓN DEL SISTEMA DE ANÁLISIS AVANZADO ===\n\n";

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Conexión a la base de datos establecida correctamente.\n\n";
    
    // Verificar tablas y columnas necesarias
    echo "Verificando estructura de la base de datos...\n";
    
    // 1. Verificar tabla de cuadre diario
    $result = $db->query("SHOW TABLES LIKE 'cuadre_diario'");
    if ($result->rowCount() > 0) {
        echo "✓ Tabla 'cuadre_diario' existe.\n";
        
        // Verificar columnas
        $result = $db->query("SHOW COLUMNS FROM cuadre_diario");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['id', 'fecha', 'total_ventas', 'total_costos', 'total_compras_proveedores', 'notas'];
        
        $missingColumns = array_diff($requiredColumns, $columns);
        if (empty($missingColumns)) {
            echo "  ✓ Todas las columnas requeridas existen en 'cuadre_diario'.\n";
        } else {
            echo "  ✗ Faltan columnas en 'cuadre_diario': " . implode(', ', $missingColumns) . "\n";
        }
    } else {
        echo "✗ Tabla 'cuadre_diario' NO existe. Ejecute el script de instalación.\n";
    }
    
    // 2. Verificar columna costo_unitario en productos_ingredientes
    $result = $db->query("SHOW COLUMNS FROM productos_ingredientes LIKE 'costo_unitario'");
    if ($result->rowCount() > 0) {
        echo "✓ Columna 'costo_unitario' existe en 'productos_ingredientes'.\n";
    } else {
        echo "✗ Columna 'costo_unitario' NO existe en 'productos_ingredientes'. Ejecute el script de instalación.\n";
    }
    
    // 3. Verificar columna costo_total en detalles_pedido
    $result = $db->query("SHOW COLUMNS FROM detalles_pedido LIKE 'costo_total'");
    if ($result->rowCount() > 0) {
        echo "✓ Columna 'costo_total' existe en 'detalles_pedido'.\n";
    } else {
        echo "✗ Columna 'costo_total' NO existe en 'detalles_pedido'. Ejecute el script de instalación.\n";
    }
    
    // 4. Verificar tabla de proveedores
    $result = $db->query("SHOW TABLES LIKE 'proveedores'");
    if ($result->rowCount() > 0) {
        echo "✓ Tabla 'proveedores' existe.\n";
        
        // Verificar si hay datos
        $result = $db->query("SELECT COUNT(*) FROM proveedores");
        $count = $result->fetchColumn();
        echo "  ✓ Hay {$count} proveedores registrados en el sistema.\n";
    } else {
        echo "✗ Tabla 'proveedores' NO existe. Ejecute el script de instalación.\n";
    }
    
    // 5. Verificar columna proveedor_id en ingredientes
    $result = $db->query("SHOW COLUMNS FROM ingredientes LIKE 'proveedor_id'");
    if ($result->rowCount() > 0) {
        echo "✓ Columna 'proveedor_id' existe en 'ingredientes'.\n";
    } else {
        echo "✗ Columna 'proveedor_id' NO existe en 'ingredientes'. Ejecute el script de instalación.\n";
    }
    
    // Verificar archivos del backend
    echo "\nVerificando archivos del backend...\n";
    
    $backendFiles = [
        '/App/models/CuadreDiarioModel.php',
        '/App/Controllers/CuadreDiarioController.php',
        '/App/routes/cuadre_diario_api.php'
    ];
    
    foreach ($backendFiles as $file) {
        if (file_exists(BASE_PATH . $file)) {
            echo "✓ Archivo " . basename($file) . " existe.\n";
        } else {
            echo "✗ Archivo " . basename($file) . " NO existe.\n";
        }
    }
    
    // Verificar archivos del frontend
    echo "\nVerificando archivos del frontend...\n";
    
    $frontendPath = __DIR__ . '/menumaster-frontend';
    $frontendFiles = [
        '/src/features/analisis/index.js',
        '/src/features/analisis/AnalisisModule.jsx',
        '/src/features/analisis/services/analisisService.js',
        '/src/features/analisis/pages/AnalisisLayout.jsx',
        '/src/features/analisis/pages/ResumenVentas.jsx',
        '/src/features/analisis/pages/RentabilidadProductos.jsx',
        '/src/features/analisis/pages/CuadreDiario.jsx',
        '/src/features/analisis/pages/InventarioProveedores.jsx'
    ];
    
    foreach ($frontendFiles as $file) {
        if (file_exists($frontendPath . $file)) {
            echo "✓ Archivo " . basename($file) . " existe.\n";
        } else {
            echo "✗ Archivo " . basename($file) . " NO existe.\n";
        }
    }
    
    echo "\n=== VERIFICACIÓN COMPLETADA ===\n\n";
    echo "Si todos los elementos están marcados con ✓, el sistema de análisis avanzado está correctamente instalado.\n";
    echo "Si hay elementos marcados con ✗, ejecute el script de instalación:\n";
    echo "php menumaster-backend/instalar_analisis_avanzado.php\n\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>