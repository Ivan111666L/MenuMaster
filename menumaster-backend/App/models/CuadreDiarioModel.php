<?php
namespace app\Models;

use PDO;
use Exception;

class CuadreDiarioModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los cuadres diarios
     */
    public function findAll($fechaInicio = null, $fechaFin = null) {
        try {
            $query = "
                SELECT cd.*, u.nombre as creado_por_nombre
                FROM cuadre_diario cd
                LEFT JOIN usuarios u ON cd.creado_por = u.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($fechaInicio) {
                $query .= " AND cd.fecha >= :fecha_inicio";
                $params[':fecha_inicio'] = $fechaInicio;
            }
            
            if ($fechaFin) {
                $query .= " AND cd.fecha <= :fecha_fin";
                $params[':fecha_fin'] = $fechaFin;
            }
            
            $query .= " ORDER BY cd.fecha DESC";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener cuadres diarios: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un cuadre diario por ID
     */
    public function findById($id) {
        try {
            $query = "
                SELECT cd.*, u.nombre as creado_por_nombre
                FROM cuadre_diario cd
                LEFT JOIN usuarios u ON cd.creado_por = u.id
                WHERE cd.id = :id
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener cuadre diario: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el cuadre diario para una fecha específica
     */
    public function findByFecha($fecha) {
        try {
            $query = "
                SELECT cd.*, u.nombre as creado_por_nombre
                FROM cuadre_diario cd
                LEFT JOIN usuarios u ON cd.creado_por = u.id
                WHERE cd.fecha = :fecha
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener cuadre diario por fecha: " . $e->getMessage());
        }
    }

    /**
     * Crea o actualiza un cuadre diario
     */
    public function crearOActualizar($fecha, $totalComprasProveedores, $notas, $usuarioId) {
        try {
            // Verificar si ya existe un cuadre para esta fecha
            $cuadre = $this->findByFecha($fecha);
            
            if ($cuadre) {
                // Actualizar cuadre existente
                $query = "
                    UPDATE cuadre_diario
                    SET total_compras_proveedores = :total_compras_proveedores,
                        notas = :notas
                    WHERE id = :id
                ";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':total_compras_proveedores', $totalComprasProveedores, PDO::PARAM_STR);
                $stmt->bindParam(':notas', $notas, PDO::PARAM_STR);
                $stmt->bindParam(':id', $cuadre['id'], PDO::PARAM_INT);
                
                $stmt->execute();
                return $cuadre['id'];
            } else {
                // Calcular ventas y costos del día
                $ventasYCostos = $this->calcularVentasYCostosPorFecha($fecha);
                
                // Crear nuevo cuadre
                $query = "
                    INSERT INTO cuadre_diario (
                        fecha, total_ventas, total_costos, 
                        total_compras_proveedores, notas, creado_por
                    ) VALUES (
                        :fecha, :total_ventas, :total_costos,
                        :total_compras_proveedores, :notas, :creado_por
                    )
                ";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':total_ventas', $ventasYCostos['total_ventas'], PDO::PARAM_STR);
                $stmt->bindParam(':total_costos', $ventasYCostos['total_costos'], PDO::PARAM_STR);
                $stmt->bindParam(':total_compras_proveedores', $totalComprasProveedores, PDO::PARAM_STR);
                $stmt->bindParam(':notas', $notas, PDO::PARAM_STR);
                $stmt->bindParam(':creado_por', $usuarioId, PDO::PARAM_INT);
                
                $stmt->execute();
                return $this->db->lastInsertId();
            }
        } catch (Exception $e) {
            throw new Exception("Error al crear/actualizar cuadre diario: " . $e->getMessage());
        }
    }

    /**
     * Calcula las ventas y costos para una fecha específica
     */
    private function calcularVentasYCostosPorFecha($fecha) {
        try {
            $query = "
                SELECT 
                    IFNULL(SUM(hp.total), 0) as total_ventas,
                    IFNULL(SUM(hdp.costo_total), 0) as total_costos
                FROM historial_pedidos hp
                LEFT JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                WHERE DATE(hp.fecha_finalizacion) = :fecha
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_ventas' => $result['total_ventas'] ?? 0,
                'total_costos' => $result['total_costos'] ?? 0
            ];
        } catch (Exception $e) {
            throw new Exception("Error al calcular ventas y costos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el resumen de rentabilidad por producto para una fecha o rango de fechas
     */
    public function obtenerRentabilidadProductos($fechaInicio = null, $fechaFin = null) {
        try {
            $query = "
                SELECT 
                    hdp.producto_id,
                    hdp.producto_nombre,
                    SUM(hdp.cantidad) as cantidad_total,
                    AVG(hdp.precio_unitario) as precio_promedio,
                    SUM(hdp.subtotal) as ventas_totales,
                    SUM(hdp.costo_total) as costos_totales,
                    SUM(hdp.subtotal) - SUM(hdp.costo_total) as rentabilidad_total,
                    (SUM(hdp.subtotal) - SUM(hdp.costo_total)) / SUM(hdp.subtotal) * 100 as porcentaje_rentabilidad
                FROM historial_detalles_pedido hdp
                JOIN historial_pedidos hp ON hdp.historial_pedido_id = hp.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($fechaInicio) {
                $query .= " AND DATE(hp.fecha_finalizacion) >= :fecha_inicio";
                $params[':fecha_inicio'] = $fechaInicio;
            }
            
            if ($fechaFin) {
                $query .= " AND DATE(hp.fecha_finalizacion) <= :fecha_fin";
                $params[':fecha_fin'] = $fechaFin;
            }
            
            $query .= " GROUP BY hdp.producto_id, hdp.producto_nombre ORDER BY rentabilidad_total DESC";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener rentabilidad de productos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el resumen de ventas diarias para un rango de fechas
     */
    public function obtenerResumenVentasDiarias($fechaInicio = null, $fechaFin = null) {
        try {
            if (!$fechaInicio) {
                $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            }
            
            if (!$fechaFin) {
                $fechaFin = date('Y-m-d');
            }
            
            $query = "
                SELECT 
                    DATE(hp.fecha_finalizacion) as fecha,
                    COUNT(DISTINCT hp.id) as total_pedidos,
                    SUM(hp.total) as total_ventas,
                    SUM(hdp.costo_total) as total_costos,
                    SUM(hp.total) - SUM(hdp.costo_total) as rentabilidad
                FROM historial_pedidos hp
                LEFT JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                WHERE DATE(hp.fecha_finalizacion) BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY DATE(hp.fecha_finalizacion)
                ORDER BY fecha DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener resumen de ventas diarias: " . $e->getMessage());
        }
    }
}
?>