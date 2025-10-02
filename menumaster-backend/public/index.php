<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// ===== INICIO DEL CÓDIGO CORS =====
// Permitir solicitudes desde tu frontend (desarrollo y vista previa de Vite)
$allowedOrigins = [
    'http://localhost:5173', // Vite dev server
    'http://localhost:5174', // Vite dev alt port
    'http://localhost:4173', // Vite preview server
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Fallback para desarrollo local
    header("Access-Control-Allow-Origin: http://localhost:5173");
}
// Permitir los métodos HTTP que tu API utiliza
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Permitir las cabeceras que el frontend pueda enviar (Authorization es clave para tokens)
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// Asegurar que proxies caches respeten el origen
header("Vary: Origin");
// Si se usan cookies/sesión, permitir credenciales (JWT por header no requiere esto, pero no afecta)
header("Access-Control-Allow-Credentials: true");

// Manejar la solicitud de pre-vuelo (preflight) del navegador
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // No es necesario procesar nada más, solo enviar las cabeceras y salir.
    http_response_code(204); // 204 No Content
    ob_end_clean();
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