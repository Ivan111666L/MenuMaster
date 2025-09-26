<?php
// routes/security_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/SecurityController.php';
require_once BASE_PATH . '/App/Models/LoginAttemptsModel.php';
require_once BASE_PATH . '/App/Models/UserActivityLogModel.php';
require_once BASE_PATH . '/App/Models/PasswordResetTokensModel.php';
require_once BASE_PATH . '/App/Models/UsuarioModel.php';

use App\Controllers\SecurityController;
use App\Models\LoginAttemptsModel;
use App\Models\UserActivityLogModel;
use App\Models\PasswordResetTokensModel;
use App\Models\UsuarioModel;

try {
    // Instanciar el controlador
    $controller = new SecurityController($db);

    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $security_index = array_search('security', $uri_segments);
    $action = $uri_segments[$security_index + 1] ?? null;
    $id = $uri_segments[$security_index + 2] ?? null;

    // Dirigir la petición al método correcto del controlador
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'registrar-intento-fallido':
                $controller->registrarIntentoFallido();
                break;

            case 'limpiar-intentos':
                $controller->limpiarIntentos();
                break;

            case 'registrar-actividad':
                $controller->registrarActividad();
                break;

            case 'crear-token-reset':
                $controller->crearTokenReset();
                break;

            case 'verificar-token-reset':
                $controller->verificarTokenReset();
                break;

            case 'resetear-password':
                $controller->resetearPassword();
                break;

            case 'registrar-login-exitoso':
                $controller->registrarLoginExitoso();
                break;

            case 'registrar-logout':
                $controller->registrarLogout();
                break;

            default:
                throw new Exception("Acción de seguridad no encontrada", 404);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            case 'verificar-bloqueo':
                if ($id) {
                    $controller->verificarBloqueo($id);
                } else {
                    throw new Exception("Email requerido para verificar bloqueo", 400);
                }
                break;

            case 'estadisticas-login':
                $controller->getEstadisticasLogin();
                break;

            case 'actividades-usuario':
                if ($id) {
                    $controller->getActividadesUsuario($id);
                } else {
                    throw new Exception("ID de usuario requerido", 400);
                }
                break;

            case 'actividades':
                $controller->getActividades();
                break;

            case 'estadisticas-actividad':
                $controller->getEstadisticasActividad();
                break;

            case 'usuarios-mas-activos':
                $controller->getUsuariosMasActivos();
                break;

            case 'actividades-sospechosas':
                $controller->getActividadesSospechosas();
                break;

            case 'estadisticas-tokens':
                $controller->getEstadisticasTokens();
                break;

            case 'reporte-seguridad':
                $controller->getReporteSeguridad();
                break;

            default:
                throw new Exception("Acción de consulta de seguridad no encontrada", 404);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        switch ($action) {
            case 'limpiar-datos-antiguos':
                $controller->limpiarDatosAntiguos();
                break;

            default:
                throw new Exception("Acción de eliminación de seguridad no encontrada", 404);
        }
    } else {
        throw new Exception("Método HTTP no permitido para esta ruta", 405);
    }

} catch (Exception $e) {
    // Manejo de errores
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode() ?: 500
    ]);
}
?>
