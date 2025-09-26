<?php

namespace App\Controllers;

use App\Models\ComprasProveedorModel;
use App\Models\DetalleCompraProveedorModel;
use App\Models\ProveedorIngredienteModel;
use App\Models\IngredienteModel;
use App\Models\ProveedorModel;
use Exception;

class ComprasController extends Controller
{
    private $comprasModel;
    private $detalleComprasModel;
    private $proveedorIngredienteModel;
    private $ingredienteModel;
    private $proveedorModel;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->comprasModel = new ComprasProveedorModel($db);
        $this->detalleComprasModel = new DetalleCompraProveedorModel($db);
        $this->proveedorIngredienteModel = new ProveedorIngredienteModel($db);
        $this->ingredienteModel = new IngredienteModel($db);
        $this->proveedorModel = new ProveedorModel($db);
    }

    /**
     * Crear nueva compra a proveedor
     */
    public function crearCompra()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $proveedorId = $data['proveedor_id'] ?? null;
            $usuarioId = $data['usuario_id'] ?? null;
            $fechaEntregaEsperada = $data['fecha_entrega_esperada'] ?? null;
            $observaciones = $data['observaciones'] ?? null;
            $detalles = $data['detalles'] ?? [];

            if (!$proveedorId || !$usuarioId || empty($detalles)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Proveedor ID, Usuario ID y detalles son requeridos'
                ], 400);
                return;
            }

            // Crear la compra
            $compraId = $this->comprasModel->crearCompra(
                $proveedorId,
                $usuarioId,
                $fechaEntregaEsperada,
                $observaciones
            );

            if (!$compraId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al crear la compra'
                ], 500);
                return;
            }

            // Crear los detalles de la compra
            $detallesCreados = [];
            $totalCompra = 0;

            foreach ($detalles as $detalle) {
                $ingredienteId = $detalle['ingrediente_id'] ?? null;
                $cantidad = $detalle['cantidad'] ?? null;
                $precioUnitario = $detalle['precio_unitario'] ?? null;

                if ($ingredienteId && $cantidad && $precioUnitario) {
                    $detalleId = $this->detalleComprasModel->crearDetalle(
                        $compraId,
                        $ingredienteId,
                        $cantidad,
                        $precioUnitario,
                        $detalle['cantidad_recibida'] ?? null
                    );

                    if ($detalleId) {
                        $subtotal = $cantidad * $precioUnitario;
                        $totalCompra += $subtotal;
                        
                        $detallesCreados[] = [
                            'id' => $detalleId,
                            'ingrediente_id' => $ingredienteId,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitario,
                            'subtotal' => $subtotal
                        ];
                    }
                }
            }

            // Actualizar el total de la compra
            $this->comprasModel->actualizarTotal($compraId, $totalCompra);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Compra creada exitosamente',
                'data' => [
                    'compra_id' => $compraId,
                    'total' => $totalCompra,
                    'detalles' => $detallesCreados
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al crear compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las compras con filtros
     */
    public function getCompras()
    {
        try {
            $filtros = [
                'proveedor_id' => $_GET['proveedor_id'] ?? null,
                'estado' => $_GET['estado'] ?? null,
                'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_GET['fecha_fin'] ?? null,
                'usuario_id' => $_GET['usuario_id'] ?? null,
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0
            ];

            $compras = $this->comprasModel->getCompras($filtros);

            $this->jsonResponse([
                'success' => true,
                'data' => $compras,
                'total' => count($compras)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener compras: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una compra específica con sus detalles
     */
    public function getCompra($compraId)
    {
        try {
            $compra = $this->comprasModel->getCompraPorId($compraId);
            
            if (!$compra) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Compra no encontrada'
                ], 404);
                return;
            }

            $detalles = $this->detalleComprasModel->getDetallesPorCompra($compraId);

            $compra['detalles'] = $detalles;

            $this->jsonResponse([
                'success' => true,
                'data' => $compra
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de una compra
     */
    public function actualizarEstado($compraId)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $nuevoEstado = $data['estado'] ?? null;

            if (!$nuevoEstado) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Estado es requerido'
                ], 400);
                return;
            }

            $resultado = $this->comprasModel->actualizarEstado($compraId, $nuevoEstado);

            if ($resultado) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al actualizar estado'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar compra como recibida y actualizar inventario
     */
    public function marcarComoRecibida($compraId)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $fechaRecepcion = $data['fecha_recepcion'] ?? date('Y-m-d H:i:s');
            $observacionesRecepcion = $data['observaciones_recepcion'] ?? null;
            $detallesRecepcion = $data['detalles_recepcion'] ?? [];

            $resultado = $this->comprasModel->marcarComoRecibida(
                $compraId, 
                $fechaRecepcion, 
                $observacionesRecepcion,
                $detallesRecepcion
            );

            if ($resultado) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Compra marcada como recibida e inventario actualizado'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al marcar compra como recibida'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al procesar recepción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar orden de compra automática
     */
    public function generarOrdenAutomatica()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuarioId = $data['usuario_id'] ?? null;

            if (!$usuarioId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario ID es requerido'
                ], 400);
                return;
            }

            $ordenesGeneradas = $this->comprasModel->generarOrdenAutomatica($usuarioId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Órdenes automáticas generadas',
                'data' => $ordenesGeneradas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al generar órdenes automáticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de compras
     */
    public function getEstadisticasCompras()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $estadisticas = $this->comprasModel->getEstadisticasCompras($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener análisis de proveedores
     */
    public function getAnalisisProveedores()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $analisis = $this->comprasModel->getAnalisisProveedores($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $analisis
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener análisis de proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ingredientes más comprados
     */
    public function getIngredientesMasComprados()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $limit = $_GET['limit'] ?? 10;

            $ingredientes = $this->detalleComprasModel->getIngredientesMasComprados($limit, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $ingredientes
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener ingredientes más comprados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener análisis de variación de precios
     */
    public function getAnalisisPrecios()
    {
        try {
            $ingredienteId = $_GET['ingrediente_id'] ?? null;
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            if ($ingredienteId) {
                $analisis = $this->detalleComprasModel->getVariacionPrecios($ingredienteId, $fechaInicio, $fechaFin);
            } else {
                $analisis = $this->detalleComprasModel->getAnalisisPrecios($fechaInicio, $fechaFin);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $analisis
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener análisis de precios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener discrepancias en recepciones
     */
    public function getDiscrepanciasRecepcion()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $discrepancias = $this->detalleComprasModel->getDiscrepanciasRecepcion($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $discrepancias
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener discrepancias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener eficiencia de proveedores
     */
    public function getEficienciaProveedores()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $eficiencia = $this->detalleComprasModel->getEficienciaProveedores($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $eficiencia
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener eficiencia de proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de precios de un ingrediente
     */
    public function getHistorialPrecios($ingredienteId)
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $historial = $this->detalleComprasModel->getHistorialPreciosIngrediente($ingredienteId, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $historial
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener historial de precios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar detalle de compra
     */
    public function actualizarDetalle($detalleId)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $cantidad = $data['cantidad'] ?? null;
            $precioUnitario = $data['precio_unitario'] ?? null;
            $cantidadRecibida = $data['cantidad_recibida'] ?? null;

            $resultado = $this->detalleComprasModel->actualizarDetalle(
                $detalleId,
                $cantidad,
                $precioUnitario,
                $cantidadRecibida
            );

            if ($resultado) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Detalle actualizado correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al actualizar detalle'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar detalle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar compra
     */
    public function eliminarCompra($compraId)
    {
        try {
            // Verificar que la compra no esté en estado 'recibida'
            $compra = $this->comprasModel->getCompraPorId($compraId);
            
            if (!$compra) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Compra no encontrada'
                ], 404);
                return;
            }

            if ($compra['estado'] === 'recibida') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se puede eliminar una compra ya recibida'
                ], 400);
                return;
            }

            $resultado = $this->comprasModel->eliminarCompra($compraId);

            if ($resultado) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Compra eliminada correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al eliminar compra'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al eliminar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sugerencias de compra basadas en stock mínimo
     */
    public function getSugerenciasCompra()
    {
        try {
            $compraId = $_GET['compra_id'] ?? null;

            if (!$compraId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Compra ID es requerido'
                ], 400);
                return;
            }

            $sugerencias = $this->comprasModel->getSugerenciasCompra($compraId);
            if ($sugerencias) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => $sugerencias
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se encontraron sugerencias de compra'
                ], 404);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener sugerencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proyección de costos
     */
    public function getProyeccionCostos()
    {
        try {
            $ingredienteId = $_GET['ingrediente_id'] ?? null;
            $cantidad = $_GET['cantidad'] ?? null;

            if (!$ingredienteId || !$cantidad) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ingrediente ID y cantidad son requeridos'
                ], 400);
                return;
            }

            $proyeccion = $this->detalleComprasModel->getProyeccionCostos($ingredienteId, $cantidad);

            $this->jsonResponse([
                'success' => true,
                'data' => $proyeccion
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener proyección de costos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reporte completo de compras
     */
    public function getReporteCompleto()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = [
                'estadisticas_generales' => $this->comprasModel->getEstadisticasCompras($fechaInicio, $fechaFin),
                'analisis_proveedores' => $this->comprasModel->getAnalisisProveedores($fechaInicio, $fechaFin),
                'ingredientes_mas_comprados' => $this->detalleComprasModel->getIngredientesMasComprados(10, $fechaInicio, $fechaFin),
                'analisis_precios' => $this->detalleComprasModel->getAnalisisPrecios($fechaInicio, $fechaFin),
                'discrepancias_recepcion' => $this->detalleComprasModel->getDiscrepanciasRecepcion($fechaInicio, $fechaFin),
                'eficiencia_proveedores' => $this->detalleComprasModel->getEficienciaProveedores($fechaInicio, $fechaFin),
                'sugerencias_compra' => $this->proveedorIngredienteModel->getSugerenciasCompra()
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $reporte,
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
     * Gestionar relaciones proveedor-ingrediente
     */
    public function crearRelacionProveedorIngrediente()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $proveedorId = $data['proveedor_id'] ?? null;
            $ingredienteId = $data['ingrediente_id'] ?? null;
            $precioUnitario = $data['precio_unitario'] ?? null;
            $tiempoEntrega = $data['tiempo_entrega_dias'] ?? null;
            $cantidadMinima = $data['cantidad_minima_pedido'] ?? null;

            if (!$proveedorId || !$ingredienteId || !$precioUnitario) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Proveedor ID, Ingrediente ID y precio son requeridos'
                ], 400);
                return;
            }

            $relacionId = $this->proveedorIngredienteModel->crearRelacion(
                $proveedorId,
                $ingredienteId,
                $precioUnitario,
                $tiempoEntrega,
                $cantidadMinima
            );

            if ($relacionId) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Relación proveedor-ingrediente creada',
                    'data' => ['id' => $relacionId]
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al crear relación'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al crear relación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener mejores proveedores para un ingrediente
     */
    public function getMejoresProveedores($ingredienteId)
    {
        try {
            $proveedores = $this->proveedorIngredienteModel->getMejoresProveedores($ingredienteId);

            $this->jsonResponse([
                'success' => true,
                'data' => $proveedores
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener mejores proveedores: ' . $e->getMessage()
            ], 500);
        }
    }
}