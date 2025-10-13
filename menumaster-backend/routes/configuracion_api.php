<?php
use App\Controllers\ConfiguracionController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;

require_once BASE_PATH . '/App/Controllers/ConfiguracionController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/App/Middleware/RolMiddleware.php';

$db = $db ?? null; // reutiliza conexión si existe
if (!$db instanceof PDO) {
    throw new RuntimeException('Expected PDO instance for ConfiguracionController');
}
$controller = new ConfiguracionController($db);
$auth = new AuthMiddleware();
$roles = new RolMiddleware();

// Rutas: /api/configuracion (GET, POST)
if ($requestMethod === 'GET') {
    // Autenticación simple antes de ejecutar el controlador
    $auth->requireAuth();
    $controller->getConfiguraciones();
    return;
}

if ($requestMethod === 'POST') {
    // Requiere autenticación y permisos (admin o permiso granular de configuración)
    $auth->requireAuth();
    if (!$roles->isAdmin()) {
        // Si no es admin, exigir permiso granular por módulo/acción
        $roles->requireModulePermission('configuracion', 'editar');
    }
    $controller->saveConfiguraciones();
    return;
}

throw new Exception("Método {$requestMethod} no soportado en /configuracion", 405);
?>