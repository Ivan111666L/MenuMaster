<?php
// routes/historial_api.php

// --- Dependencias ---
require_once BASE_PATH . '/App/Controllers/HistorialController.php';
require_once BASE_PATH . '/App/Models/HistorialPedidosModel.php';
require_once BASE_PATH . '/App/Models/HistorialDetallesPedidoModel.php';

use App\Controllers\HistorialController;
use App\Models\HistorialPedidosModel;
use App\Models\HistorialDetallesPedidoModel;

try {
    // Instanciar el controlador
    $controller = new HistorialController($db);

    // Analizar la petición
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', trim($request_uri, '/'));
    $historial_index = array_search('historial', $uri_segments);
    $action = $uri_segments[$historial_index + 1] ?? null;
    $id = $uri_segments[$historial_index + 2] ?? null;

    // Dirigir la petición al método correcto del controlador
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            case 'pedidos':
                $controller->getHistorialPedidos();
                break;

            case 'pedido':
                if ($id) {
                    $controller->getDetallesPedidoHistorico($id);
                } else {
                    throw new Exception("ID de pedido requerido", 400);
                }
                break;

            case 'estadisticas':
                $controller->getEstadisticasVentas();
                break;

            case 'productos-mas-vendidos':
                $controller->getProductosMasVendidos();
                break;

            case 'ventas-por-mesero':
                $controller->getVentasPorMesero();
                break;

            case 'analisis-rentabilidad':
                $controller->getAnalisisRentabilidad();
                break;

            case 'productos-mas-rentables':
                $controller->getProductosMasRentables();
                break;

            case 'productos-menos-rentables':
                $controller->getProductosMenosRentables();
                break;

            case 'ventas-por-dia':
                $controller->getVentasPorDia();
                break;

            case 'ventas-por-mes':
                $controller->getVentasPorMes();
                break;

            case 'horarios-pico':
                $controller->getHorariosPico();
                break;

            case 'analisis-costos':
                if ($id) {
                    $controller->getAnalisisCostosProducto($id);
                } else {
                    throw new Exception("ID de producto requerido", 400);
                }
                break;

            case 'reporte-completo':
                $controller->getReporteCompleto();
                break;

            case 'comparativa-periodos':
                $controller->getComparativaPeriodos();
                break;

            case 'exportar':
                $controller->exportarDatos();
                break;

            default:
                // Si no hay acción específica, mostrar historial de pedidos por defecto
                $controller->getHistorialPedidos();
                break;
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
