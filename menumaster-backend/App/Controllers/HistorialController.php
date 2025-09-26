<?php

namespace App\Controllers;

use App\Models\HistorialPedidosModel;
use App\Models\HistorialDetallesPedidoModel;
use Exception;

class HistorialController extends Controller
{
    private $historialPedidosModel;
    private $historialDetallesModel;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->historialPedidosModel = new HistorialPedidosModel($db);
        $this->historialDetallesModel = new HistorialDetallesPedidoModel($db);
    }

    /**
     * Obtener historial de pedidos con filtros
     */
    public function getHistorialPedidos()
    {
        try {
            $filtros = [
                'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_GET['fecha_fin'] ?? null,
                'usuario_id' => $_GET['usuario_id'] ?? null,
                'mesa_id' => $_GET['mesa_id'] ?? null,
                'estado' => $_GET['estado'] ?? null,
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0
            ];

            $historial = $this->historialPedidosModel->getHistorialPedidos($filtros);

            $this->jsonResponse([
                'success' => true,
                'data' => $historial,
                'total' => count($historial)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener historial de pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un pedido histórico
     */
    public function getDetallesPedidoHistorico($pedidoId)
    {
        try {
            $detalles = $this->historialDetallesModel->getDetallesPorPedido($pedidoId);

            if (empty($detalles)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se encontraron detalles para este pedido'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $detalles
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener detalles del pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de ventas
     */
    public function getEstadisticasVentas()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $estadisticas = $this->historialPedidosModel->getEstadisticasVentas($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener estadísticas de ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos más vendidos
     */
    public function getProductosMasVendidos()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $limit = $_GET['limit'] ?? 10;

            $productos = $this->historialPedidosModel->getProductosMasVendidos($limit, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $productos
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener productos más vendidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas por mesero
     */
    public function getVentasPorMesero()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $ventas = $this->historialPedidosModel->getVentasPorMesero($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $ventas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener ventas por mesero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener análisis de rentabilidad
     */
    public function getAnalisisRentabilidad()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $rentabilidad = $this->historialDetallesModel->getAnalisisRentabilidad($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $rentabilidad
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener análisis de rentabilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos más rentables
     */
    public function getProductosMasRentables()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $limit = $_GET['limit'] ?? 10;

            $productos = $this->historialDetallesModel->getProductosMasRentables($limit, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $productos
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener productos más rentables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos menos rentables
     */
    public function getProductosMenosRentables()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $limit = $_GET['limit'] ?? 10;

            $productos = $this->historialDetallesModel->getProductosMenosRentables($limit, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $productos
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener productos menos rentables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas por día
     */
    public function getVentasPorDia()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $ventas = $this->historialPedidosModel->getVentasPorDia($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $ventas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener ventas por día: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas por mes
     */
    public function getVentasPorMes()
    {
        try {
            $año = $_GET['año'] ?? null;

            $ventas = $this->historialPedidosModel->getVentasPorMes($año);

            $this->jsonResponse([
                'success' => true,
                'data' => $ventas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener ventas por mes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener horarios pico
     */
    public function getHorariosPico()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $horarios = $this->historialPedidosModel->getHorariosPico($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $horarios
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener horarios pico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener análisis de costos por producto
     */
    public function getAnalisisCostosProducto($productoId)
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $costos = $this->historialDetallesModel->getAnalisisCostosProducto($productoId, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $costos
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener análisis de costos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reporte completo de ventas
     */
    public function getReporteCompleto()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            // Obtener diferentes métricas
            $estadisticas = $this->historialPedidosModel->getEstadisticasVentas($fechaInicio, $fechaFin);
            $productosMasVendidos = $this->historialPedidosModel->getProductosMasVendidos(10, $fechaInicio, $fechaFin);
            $ventasPorMesero = $this->historialPedidosModel->getVentasPorMesero($fechaInicio, $fechaFin);
            $ventasPorDia = $this->historialPedidosModel->getVentasPorDia($fechaInicio, $fechaFin);
            $horariosPico = $this->historialPedidosModel->getHorariosPico($fechaInicio, $fechaFin);
            $rentabilidad = $this->historialDetallesModel->getAnalisisRentabilidad($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'estadisticas_generales' => $estadisticas,
                    'productos_mas_vendidos' => $productosMasVendidos,
                    'ventas_por_mesero' => $ventasPorMesero,
                    'ventas_por_dia' => $ventasPorDia,
                    'horarios_pico' => $horariosPico,
                    'analisis_rentabilidad' => $rentabilidad
                ],
                'periodo' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al generar reporte completo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener comparativa de períodos
     */
    public function getComparativaPeriodos()
    {
        try {
            $fechaInicio1 = $_GET['fecha_inicio_1'] ?? null;
            $fechaFin1 = $_GET['fecha_fin_1'] ?? null;
            $fechaInicio2 = $_GET['fecha_inicio_2'] ?? null;
            $fechaFin2 = $_GET['fecha_fin_2'] ?? null;

            if (!$fechaInicio1 || !$fechaFin1 || !$fechaInicio2 || !$fechaFin2) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Se requieren las fechas de ambos períodos para la comparación'
                ], 400);
                return;
            }

            $periodo1 = $this->historialPedidosModel->getEstadisticasVentas($fechaInicio1, $fechaFin1);
            $periodo2 = $this->historialPedidosModel->getEstadisticasVentas($fechaInicio2, $fechaFin2);

            // Calcular diferencias
            $comparativa = [
                'periodo_1' => [
                    'fechas' => ['inicio' => $fechaInicio1, 'fin' => $fechaFin1],
                    'datos' => $periodo1
                ],
                'periodo_2' => [
                    'fechas' => ['inicio' => $fechaInicio2, 'fin' => $fechaFin2],
                    'datos' => $periodo2
                ],
                'diferencias' => [
                    'total_pedidos' => ($periodo1['total_pedidos'] ?? 0) - ($periodo2['total_pedidos'] ?? 0),
                    'total_ventas' => ($periodo1['total_ventas'] ?? 0) - ($periodo2['total_ventas'] ?? 0),
                    'promedio_pedido' => ($periodo1['promedio_pedido'] ?? 0) - ($periodo2['promedio_pedido'] ?? 0)
                ]
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $comparativa
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al generar comparativa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar datos históricos
     */
    public function exportarDatos()
    {
        try {
            $formato = $_GET['formato'] ?? 'json';
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $tipo = $_GET['tipo'] ?? 'pedidos'; // pedidos, detalles, estadisticas

            switch ($tipo) {
                case 'pedidos':
                    $datos = $this->historialPedidosModel->getHistorialPedidos([
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]);
                    break;
                case 'estadisticas':
                    $datos = $this->historialPedidosModel->getEstadisticasVentas($fechaInicio, $fechaFin);
                    break;
                default:
                    $datos = $this->historialPedidosModel->getHistorialPedidos([
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]);
            }

            if ($formato === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="historial_' . $tipo . '_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                
                if (!empty($datos)) {
                    // Escribir encabezados
                    fputcsv($output, array_keys($datos[0]));
                    
                    // Escribir datos
                    foreach ($datos as $fila) {
                        fputcsv($output, $fila);
                    }
                }
                
                fclose($output);
            } else {
                $this->jsonResponse([
                    'success' => true,
                    'data' => $datos,
                    'tipo' => $tipo,
                    'periodo' => [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]
                ]);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al exportar datos: ' . $e->getMessage()
            ], 500);
        }
    }
}