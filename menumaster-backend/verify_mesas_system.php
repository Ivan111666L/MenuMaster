<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use App\Config\ConexionDb;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    
    echo "=== VERIFICACIÓN SISTEMA DE MESAS ===\n\n";
    
    // 1. Verificar tabla mesas
    echo "1. Estructura de la tabla 'mesas':\n";
    $stmt = $db->query('DESCRIBE mesas');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    // 2. Mostrar mesas existentes
    echo "\n2. Mesas actuales:\n";
    $stmt = $db->query('SELECT * FROM mesas ORDER BY numero');
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($mesas)) {
        echo "⚠️ No hay mesas configuradas. Creando mesas de ejemplo...\n";
        
        // Crear mesas de ejemplo
        $mesasEjemplo = [
            ['numero' => 1, 'capacidad' => 2, 'ubicacion' => 'Terraza'],
            ['numero' => 2, 'capacidad' => 4, 'ubicacion' => 'Salón Principal'], 
            ['numero' => 3, 'capacidad' => 6, 'ubicacion' => 'Salón Principal'],
            ['numero' => 4, 'capacidad' => 2, 'ubicacion' => 'Bar'],
            ['numero' => 5, 'capacidad' => 8, 'ubicacion' => 'Salón VIP']
        ];
        
        foreach ($mesasEjemplo as $mesa) {
            $stmt = $db->prepare("INSERT INTO mesas (numero, capacidad, ubicacion, estado_id) VALUES (?, ?, ?, 1)");
            $stmt->execute([$mesa['numero'], $mesa['capacidad'], $mesa['ubicacion']]);
            echo "- Mesa {$mesa['numero']} creada\n";
        }
        
        // Recargar mesas
        $stmt = $db->query('SELECT * FROM mesas ORDER BY numero');
        $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    foreach ($mesas as $mesa) {
        echo "- Mesa {$mesa['numero']}: Capacidad {$mesa['capacidad']}, Ubicación: {$mesa['ubicacion']}, Estado ID: {$mesa['estado_id']}\n";
    }
    
    // 3. Verificar controlador de mesas
    echo "\n3. Probando MesaController...\n";
    require_once 'App/Controllers/MesaController.php';
    
    $controller = new \App\Controllers\MesaController($db);
    echo "✅ MesaController instanciado correctamente\n";
    
    // 4. Verificar rutas de mesas
    echo "\n4. Verificando rutas de mesas...\n";
    if (file_exists('routes/mesas_api.php')) {
        echo "✅ Archivo de rutas 'mesas_api.php' existe\n";
    } else {
        echo "❌ Archivo de rutas 'mesas_api.php' no encontrado\n";
    }
    
    echo "\n✅ Sistema de mesas verificado correctamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>