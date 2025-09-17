<?php

// ===== INICIO DEL CÓDIGO CORS =====
// Permitir solicitudes desde tu frontend (ajusta el puerto si es diferente)
header("Access-Control-Allow-Origin: http://localhost:5173");
// Permitir los métodos HTTP que tu API utiliza
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Permitir las cabeceras que el frontend pueda enviar (Authorization es clave para tokens)
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar la solicitud de pre-vuelo (preflight) del navegador
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // No es necesario procesar nada más, solo enviar las cabeceras y salir.
    http_response_code(204); // 204 No Content
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