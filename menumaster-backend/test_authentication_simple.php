<?php
// Configurar el entorno
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar dependencias
require_once __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Simular el entorno de API
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

// Datos de login
$loginData = [
    'email' => 'admin@menumaster.com',
    'password' => 'admin123'
];

// Simular el input JSON usando un stream temporal
$tempFile = tmpfile();
fwrite($tempFile, json_encode($loginData));
rewind($tempFile);

// Redirigir php://input al archivo temporal
stream_wrapper_unregister("php");
stream_wrapper_register("php", "TestInputWrapper");
TestInputWrapper::$data = json_encode($loginData);

class TestInputWrapper {
    public static $data = '';
    private $position = 0;
    
    public function stream_open($path, $mode, $options, &$opened_path) {
        if ($path === 'php://input') {
            $this->position = 0;
            return true;
        }
        return false;
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

echo "=== PRUEBA DE AUTENTICACIÓN SIMPLE ===\n";
echo "Datos de login: " . json_encode($loginData) . "\n\n";

try {
    // Requerir el controlador
    require_once __DIR__ . '/App/Controllers/AuthController.php';
    
    // Crear instancia del controlador
    $authController = new App\Controllers\AuthController();
    
    // Capturar la salida
    ob_start();
    
    // Intentar login
    $authController->login();
    
    // Obtener la salida
    $output = ob_get_clean();
    
    echo "Respuesta del servidor:\n";
    echo $output . "\n";
    
    // Intentar decodificar la respuesta
    $response = json_decode($output, true);
    if ($response) {
        echo "\nRespuesta decodificada:\n";
        print_r($response);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Restaurar el wrapper original
stream_wrapper_restore("php");

echo "\n=== FIN DE PRUEBA ===\n";