<?php
// Detailed product creation debug
echo "=== DETAILED PRODUCT CREATION DEBUG ===\n";

// Set up environment
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Include necessary files
require_once BASE_PATH . '/app/config/ConexionDb.php';
require_once BASE_PATH . '/app/Controllers/ProductoController.php';
require_once BASE_PATH . '/app/Models/ProductoModel.php';
require_once BASE_PATH . '/app/Models/ProductoIngredientesModel.php';

use App\Config\ConexionDb;
use App\Controllers\ProductoController;

try {
    // Get database connection
    $db = ConexionDb::getConnection();
    echo "✅ Database connection established\n";

    // Create controller
    $controller = new ProductoController($db);
    echo "✅ ProductoController created\n";

    // Simulate POST data
    $testData = [
        'nombre' => 'Debug Test Product ' . time(),
        'descripcion' => 'A test product for debugging',
        'precio' => 25.50,
        'categoria_id' => 2,
        'tiempo_preparacion_min' => 15
    ];

    echo "Test data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";

    // Simulate the input stream
    $tempFile = tmpfile();
    fwrite($tempFile, json_encode($testData));
    rewind($tempFile);
    
    // Override php://input for testing
    stream_wrapper_unregister("php");
    stream_wrapper_register("php", "TestInputWrapper");
    TestInputWrapper::$data = json_encode($testData);

    // Call the store method
    echo "Calling store method...\n";
    ob_start();
    $controller->store();
    $output = ob_get_clean();
    
    echo "Controller output: $output\n";
    echo "✅ Product creation completed successfully\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test input wrapper class
class TestInputWrapper {
    public static $data = '';
    private $position = 0;

    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->position = 0;
        return true;
    }

    public function stream_read($count) {
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof() {
        return $this->position >= strlen(self::$data);
    }

    public function stream_stat() {
        return array();
    }
}