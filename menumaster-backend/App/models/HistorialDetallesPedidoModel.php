<?php

namespace App\Models;

use PDO;
use PDOException;

class HistorialDetallesPedidoModel extends Model
{
    protected $table = 'historial_detalles_pedido';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Crear detalles de historial para un pedido
     */
    public function crearDetallesHistorial($historialPedidoId, $detalles)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                historial_pedido_id, producto_id, producto_nombre, 
                cantidad, precio_unitario, subtotal, costo_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);

            foreach ($detalles as $detalle) {
                $stmt->execute([
                    $historialPedidoId,
                    $detalle['producto_id'],
                    $detalle['producto_nombre'],
                    $detalle['cantidad'],
                    $detalle['precio_unitario'],
                    $detalle['subtotal'],
                    $detalle['costo_total'] ?? 0
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al crear detalles de historial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener detalles de un pedido histórico
     */
    public function getDetallesPorHistorial($historialPedidoId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE historial_pedido_id = ? 
                    ORDER BY id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$historialPedidoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalles de historial: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener análisis de rentabilidad por producto
     */
    public function getAnalisisRentabilidad($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        hdp.producto_nombre,
                        hdp.producto_id,
                        SUM(hdp.cantidad) as total_vendido,
                        SUM(hdp.subtotal) as total_ingresos,
                        SUM(hdp.costo_total) as total_costos,
                        SUM(hdp.rentabilidad) as rentabilidad_total,
                        AVG(hdp.rentabilidad / hdp.cantidad) as rentabilidad_unitaria,
                        (SUM(hdp.rentabilidad) / SUM(hdp.subtotal)) * 100 as margen_porcentaje
                    FROM {$this->table} hdp
                    JOIN historial_pedidos hp ON hdp.historial_pedido_id = hp.id
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(hp.fecha_finalizacion) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(hp.fecha_finalizacion) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY hdp.producto_id, hdp.producto_nombre 
                      ORDER BY rentabilidad_total DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener análisis de rentabilidad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener productos más rentables
     */
    public function getProductosMasRentables($limit = 10, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        hdp.producto_nombre,
                        SUM(hdp.rentabilidad) as rentabilidad_total,
                        SUM(hdp.cantidad) as total_vendido,
                        AVG(hdp.rentabilidad / hdp.cantidad) as rentabilidad_unitaria
                    FROM {$this->table} hdp
                    JOIN historial_pedidos hp ON hdp.historial_pedido_id = hp.id
                    WHERE hdp.rentabilidad > 0";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(hp.fecha_finalizacion) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(hp.fecha_finalizacion) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY hdp.producto_nombre 
                      ORDER BY rentabilidad_total DESC 
                      LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos más rentables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener productos menos rentables o con pérdidas
     */
    public function getProductosMenosRentables($limit = 10, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        hdp.producto_nombre,
                        SUM(hdp.rentabilidad) as rentabilidad_total,
                        SUM(hdp.cantidad) as total_vendido,
                        AVG(hdp.rentabilidad / hdp.cantidad) as rentabilidad_unitaria
                    FROM {$this->table} hdp
                    JOIN historial_pedidos hp ON hdp.historial_pedido_id = hp.id
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(hp.fecha_finalizacion) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(hp.fecha_finalizacion) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY hdp.producto_nombre 
                      ORDER BY rentabilidad_total ASC 
                      LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos menos rentables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular costo total de un producto basado en sus ingredientes
     */
    public function calcularCostoProducto($productoId, $cantidad = 1)
    {
        try {
            $sql = "SELECT 
                        SUM(pi.cantidad * i.precio_compra) as costo_unitario
                    FROM productos_ingredientes pi
                    JOIN ingredientes i ON pi.ingrediente_id = i.id
                    WHERE pi.producto_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productoId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($resultado['costo_unitario'] ?? 0) * $cantidad;
        } catch (PDOException $e) {
            error_log("Error al calcular costo de producto: " . $e->getMessage());
            return 0;
        }
    }

    public function getAnalisisCostosProducto($productoId, $fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        hdp.producto_nombre,
                        hdp.producto_id,
                        SUM(hdp.cantidad) as total_vendido,
                        SUM(hdp.subtotal) as total_ingresos,
                        SUM(hdp.costo_unitario * hdp.cantidad) as total_costo,
                        SUM(hdp.rentabilidad) as rentabilidad_total,
                        AVG(hdp.precio_unitario) as precio_promedio,
                        AVG(hdp.costo_unitario) as costo_promedio,
                        (SUM(hdp.subtotal) - SUM(hdp.costo_unitario * hdp.cantidad)) as ganancia_neta,
                        CASE 
                            WHEN SUM(hdp.costo_unitario * hdp.cantidad) > 0 
                            THEN ROUND(((SUM(hdp.subtotal) - SUM(hdp.costo_unitario * hdp.cantidad)) / SUM(hdp.costo_unitario * hdp.cantidad)) * 100, 2)
                            ELSE 0 
                        END as margen_ganancia_porcentaje
                    FROM {$this->table} hp
                    JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                    WHERE hdp.producto_id = ?
                      AND DATE(hp.fecha_finalizacion) BETWEEN ? AND ?
                    GROUP BY hdp.producto_id, hdp.producto_nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productoId, $fechaInicio, $fechaFin]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener análisis de costos del producto: " . $e->getMessage());
            return null;
        }
    }

    public function getDetallesPorPedido($historialPedidoId)
    {
        try {
            $sql = "SELECT 
                        hdp.*,
                        i.nombre as ingrediente_nombre,
                        i.precio_compra,
                        i.precio_venta
                    FROM {$this->table} hdp
                    JOIN ingredientes i ON hdp.ingrediente_id = i.id
                    WHERE hdp.historial_pedido_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$historialPedidoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalles de pedido: " . $e->getMessage());
            return [];
        }
    }
}
