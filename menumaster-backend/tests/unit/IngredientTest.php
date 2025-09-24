<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\ConexionDb;
use App\Models\IngredienteModel;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTING INGREDIENT FUNCTIONALITY ===\n\n";

try {
    echo "1. Conectando a base de datos...\n";
    $db = ConexionDb::getConnection();
    echo "✅ Conexión exitosa\n";

    echo "2. Creando IngredienteModel...\n";
    $ingredienteModel = new IngredienteModel($db);
    echo "✅ IngredienteModel creado\n";

    echo "3. Probando obtener ingredientes existentes...\n";
    $ingredientes = $ingredienteModel->findAll();
    echo "✅ Encontrados " . count($ingredientes) . " ingredientes\n";
    
    if (count($ingredientes) > 0) {
        echo "   Primeros 3 ingredientes:\n";
        for ($i = 0; $i < min(3, count($ingredientes)); $i++) {
            echo "   - " . $ingredientes[$i]['nombre'] . " (Stock: " . $ingredientes[$i]['stock_actual'] . ")\n";
        }
    }

    echo "\n4. Probando crear ingrediente de prueba...\n";
    
    // Primero eliminar ingrediente de prueba si existe
    $stmt = $db->prepare("DELETE FROM ingredientes WHERE nombre = 'Tomate de Prueba Test'");
    $stmt->execute();
    
    $testData = [
        'nombre' => 'Tomate de Prueba Test',
        'descripcion' => 'Tomate para testing automatizado',
        'unidad_medida' => 'kg',
        'stock_actual' => 25.5,
        'stock_minimo' => 5.0,
        'precio_compra' => 3.50
    ];

    $newId = $ingredienteModel->create($testData);
    if ($newId) {
        echo "✅ Ingrediente creado con ID: $newId\n";
        
        echo "5. Probando obtener ingrediente creado...\n";
        $ingredient = $ingredienteModel->find($newId);
        if ($ingredient) {
            echo "✅ Ingrediente recuperado: " . $ingredient['nombre'] . "\n";
            
            echo "6. Limpiando datos de prueba...\n";
            $stmt = $db->prepare("DELETE FROM ingredientes WHERE id = ?");
            $stmt->execute([$newId]);
            echo "✅ Datos de prueba eliminados\n";
        } else {
            echo "❌ No se pudo recuperar el ingrediente creado\n";
        }
    } else {
        echo "❌ Falló la creación del ingrediente\n";
        echo "   Verificando errores de base de datos...\n";
        $errorInfo = $db->errorInfo();
        if ($errorInfo[0] !== '00000') {
            echo "   Error SQL: " . $errorInfo[2] . "\n";
        }
    }

    echo "\n=== RESUMEN ===\n";
    echo "✅ Test de ingredientes completado\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>