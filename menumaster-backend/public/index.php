<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Punto de Entrada Principal y Único de la API (Front Controller).
 */

// NOTA: El bloque de cabeceras CORS ha sido eliminado de aquí.
// Apache ahora se encarga de esto a través del archivo .htaccess,
// lo cual es más eficiente.

// Se establece el tipo de contenido por defecto para las respuestas de la API.
header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");

// --- Configuración del Entorno y Autoloader ---
// Se define una constante con la ruta raíz del proyecto backend.
define('BASE_PATH', dirname(__DIR__));

// Se carga el autoloader de Composer para manejar las dependencias.
require_once BASE_PATH . '/vendor/autoload.php';

// Se cargan las variables de entorno desde el archivo .env.
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();


// --- Manejo de Errores según el Entorno ---
// Muestra errores detallados en desarrollo pero los oculta en producción.
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}


// --- Carga del Enrutador Principal ---
// Se delega el manejo de la petición al enrutador.
require_once BASE_PATH . '/routes/router.php';