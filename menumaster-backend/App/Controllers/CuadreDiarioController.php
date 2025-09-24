<?php
namespace App\Controllers;

use App\Models\CuadreDiarioModel;
use App\Models\ProductoIngredienteModel;
use App\Models\ProveedorModel;
use PDO;
use Exception;

class CuadreDiarioController {
    private $db;
    private $cuadreDiarioModel;
    private $productoIngredienteModel;
    private $proveedorModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->cuadreDiarioModel = new CuadreDiarioModel($db);
        $this->productoIngredienteModel = new ProductoIngredienteModel($db);
        $this->proveedorModel = new ProveedorModel($db);
    }

    /**
     * Obtiene todos los cuadres diarios
     */
    public function getCuadresDiarios() {
        try {
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $cuadres = $this->cuadreDiarioModel->findAll($fechaInicio, $fechaFin);
            
            // Calcular rentabilidad para cada cuadre
            foreach ($cuadres as &$cuadre) {
                $cuadre['rentabilidad'] = $cuadre['total_ventas'] - $cuadre['total_costos'] - $cuadre['total_compras_proveedores'];
                $cuadre['porcentaje_rentabilidad'] = $cuadre['total_ventas'] > 0 ? 
                    ($cuadre['rentabilidad'] / $cuadre['total_ventas'] * 100) : 0;
            }
            
            return [
                'status' => 'success',
                'data' => $cuadres
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene un cuadre diario por ID
     */
    public function getCuadreDiario($id) {
        try {
            $cuadre = $this->cuadreDiarioModel->findById($id);
            
            if (!$cuadre) {
                return [
                    'status' => 'error',
                    'message' => 'Cuadre diario no encontrado'
                ];
            }
            
            // Calcular rentabilidad
            $cuadre['rentabilidad'] = $cuadre['total_ventas'] - $cuadre['total_costos'] - $cuadre['total_compras_proveedores'];
            $cuadre['porcentaje_rentabilidad'] = $cuadre['total_ventas'] > 0 ? 
                ($cuadre['rentabilidad'] / $cuadre['total_ventas'] * 100) : 0;
            
            return [
                'status' => 'success',
                'data' => $cuadre
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Crea o actualiza un cuadre diario
     */
    public function crearOActualizarCuadreDiario() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['fecha']) || !isset($data['total_compras_proveedores'])) {
                return [
                    'status' => 'error',
                    'message' => 'Datos incompletos'
                ];
            }
            
            $fecha = $data['fecha'];
            $totalComprasProveedores = $data['total_compras_proveedores'];
            $notas = $data['notas'] ?? '';
            $usuarioId = $_SESSION['usuario_id'] ?? 1;
            
            $id = $this->cuadreDiarioModel->crearOActualizar($fecha, $totalComprasProveedores, $notas, $usuarioId);
            
            return [
                'status' => 'success',
                'message' => 'Cuadre diario guardado correctamente',
                'data' => ['id' => $id]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la rentabilidad de productos
     */
    public function getRentabilidadProductos() {
        try {
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $rentabilidad = $this->cuadreDiarioModel->obtenerRentabilidadProductos($fechaInicio, $fechaFin);
            
            return [
                'status' => 'success',
                'data' => $rentabilidad
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene el resumen de ventas diarias
     */
    public function getResumenVentasDiarias() {
        try {
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $resumen = $this->cuadreDiarioModel->obtenerResumenVentasDiarias($fechaInicio, $fechaFin);
            
            // Calcular totales generales
            $totalPedidos = 0;
            $totalVentas = 0;
            $totalCostos = 0;
            $totalRentabilidad = 0;
            
            foreach ($resumen as $dia) {
                $totalPedidos += $dia['total_pedidos'];
                $totalVentas += $dia['total_ventas'];
                $totalCostos += $dia['total_costos'];
                $totalRentabilidad += $dia['rentabilidad'];
            }
            
            $porcentajeRentabilidad = $totalVentas > 0 ? ($totalRentabilidad / $totalVentas * 100) : 0;
            
            return [
                'status' => 'success',
                'data' => [
                    'resumen_diario' => $resumen,
                    'totales' => [
                        'total_pedidos' => $totalPedidos,
                        'total_ventas' => $totalVentas,
                        'total_costos' => $totalCostos,
                        'total_rentabilidad' => $totalRentabilidad,
                        'porcentaje_rentabilidad' => $porcentajeRentabilidad
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene el inventario actual con información de proveedores
     */
    public function getInventarioConProveedores() {
        try {
            // Obtener todos los ingredientes con su stock actual
            $query = "
                SELECT 
                    i.id, i.nombre, i.unidad_medida, i.stock_actual, i.precio_unitario,
                    i.proveedor_id, p.nombre as proveedor_nombre, p.telefono as proveedor_telefono,
                    p.email as proveedor_email
                FROM ingredientes i
                LEFT JOIN proveedores p ON i.proveedor_id = p.id
                ORDER BY i.nombre
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener productos que usan cada ingrediente
            foreach ($inventario as &$ingrediente) {
                $query = "
                    SELECT 
                        p.id, p.nombre, pi.cantidad_requerida
                    FROM productos_ingredientes pi
                    JOIN productos p ON pi.producto_id = p.id
                    WHERE pi.ingrediente_id = :ingrediente_id
                ";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':ingrediente_id', $ingrediente['id'], PDO::PARAM_INT);
                $stmt->execute();
                $ingrediente['productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [
                'status' => 'success',
                'data' => $inventario
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}