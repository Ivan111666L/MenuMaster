<?php
// routes/compras_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/ComprasController.php';
require_once BASE_PATH . '/App/Models/ComprasProveedorModel.php';
require_once BASE_PATH . '/App/Models/DetalleCompraProveedorModel.php';
require_once BASE_PATH . '/App/Models/ProveedorIngredienteModel.php';
require_once BASE_PATH . '/App/Models/IngredienteModel.php';
require_once BASE_PATH . '/App/Models/ProveedorModel.php';

use App\Controllers\ComprasController;
use App\Models\ComprasProveedorModel;
use App\Models\DetalleCompraProveedorModel;
use App\Models\ProveedorIngredienteModel;
use App\Models\IngredienteModel;
use App\Models\ProveedorModel;

try {
    // Instanciar el controlador
    $controller = new ComprasController($db);

    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $compras_index = array_search('compras', $uri_segments);
    $action = $uri_segments[$compras_index + 1] ?? null;
    $id = $uri_segments[$compras_index + 2] ?? null;

    // Dirigir la petición al método correcto del controlador
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'crear':
                $controller->crearCompra();
                break;

            case 'marcar-recibida':
                if ($id) {
                    $controller->marcarComoRecibida($id);
                } else {
                    throw new Exception("ID de compra requerido", 400);
                }
                break;

            case 'generar-orden-automatica':
                $controller->generarOrdenAutomatica();
                break;

            case 'crear-relacion-proveedor-ingrediente':
                $controller->crearRelacionProveedorIngrediente();
                break;

            default:
                throw new Exception("Acción de compras no encontrada", 404);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            case 'listar':
            case null:
                $controller->getCompras();
                break;

            case 'detalle':
                if ($id) {
                    $controller->getCompra($id);
                } else {
                    throw new Exception("ID de compra requerido", 400);
                }
                break;

            case 'estadisticas':
                $controller->getEstadisticasCompras();
                break;

            case 'analisis-proveedores':
                $controller->getAnalisisProveedores();
                break;

            case 'ingredientes-mas-comprados':
                $controller->getIngredientesMasComprados();
                break;

            case 'analisis-precios':
                $controller->getAnalisisPrecios();
                break;

            case 'discrepancias-recepcion':
                $controller->getDiscrepanciasRecepcion();
                break;

            case 'eficiencia-proveedores':
                $controller->getEficienciaProveedores();
                break;

            case 'historial-precios':
                if ($id) {
                    $controller->getHistorialPrecios($id);
                } else {
                    throw new Exception("ID de ingrediente requerido", 400);
                }
                break;

            case 'sugerencias-compra':
                $controller->getSugerenciasCompra();
                break;

            case 'proyeccion-costos':
                $controller->getProyeccionCostos();
                break;

            case 'reporte-completo':
                $controller->getReporteCompleto();
                break;

            case 'mejores-proveedores':
                if ($id) {
                    $controller->getMejoresProveedores($id);
                } else {
                    throw new Exception("ID de ingrediente requerido", 400);
                }
                break;

            default:
                throw new Exception("Acción de consulta de compras no encontrada", 404);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        switch ($action) {
            case 'actualizar-estado':
                if ($id) {
                    $controller->actualizarEstado($id);
                } else {
                    throw new Exception("ID de compra requerido", 400);
                }
                break;

            case 'actualizar-detalle':
                if ($id) {
                    $controller->actualizarDetalle($id);
                } else {
                    throw new Exception("ID de detalle requerido", 400);
                }
                break;

            default:
                throw new Exception("Acción de actualización de compras no encontrada", 404);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        switch ($action) {
            case 'eliminar':
                if ($id) {
                    $controller->eliminarCompra($id);
                } else {
                    throw new Exception("ID de compra requerido", 400);
                }
                break;

            default:
                throw new Exception("Acción de eliminación de compras no encontrada", 404);
        }
    } else {
        throw new Exception("Método HTTP no permitido para esta ruta", 405);
    }

} catch (Exception $e) {
    // Manejo de errores
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode() ?: 500
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
