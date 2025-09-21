<?php

// ===== INICIO DEL CÓDIGO CORS =====
// Permitir solicitudes desde tu frontend
$allowedOrigins = ['http://localhost:5173'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

// Permitir los métodos HTTP que tu API utiliza
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Permitir las cabeceras necesarias y credenciales
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Max-Age: 86400'); // 24 horas

// Manejar la solicitud de pre-vuelo (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
// ===== FIN DEL CÓDIGO CORS =====
// Se define una constante con la ruta raíz del proyecto backend.
// dirname(__DIR__) sube un nivel desde /public a la raíz del proyecto.
define('BASE_PATH', dirname(__DIR__));

// Se carga el autoloader de Composer, que es esencial para todo lo demás.
require_once BASE_PATH . '/vendor/autoload.php';

// Se cargan las variables de entorno desde el archivo .env ubicado en la raíz.
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// --- Configuración de Cabeceras y Errores ---

// Se establece el tipo de contenido por defecto para todas las respuestas.
header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff"); // Cabecera de seguridad
// Permitir CORS desde cualquier origen (ajustar en producción)

// Muestra errores detallados en desarrollo pero los oculta en producción.
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// --- Carga del Enrutador Principal ---
// Se delega el manejo de todas las peticiones al enrutador.
require_once BASE_PATH . '/routes/router.php';