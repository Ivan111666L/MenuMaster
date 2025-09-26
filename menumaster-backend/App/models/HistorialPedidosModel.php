<?php

namespace App\Models;

use PDO;
use PDOException;

class HistorialPedidosModel extends Model
{
    protected $table = 'historial_pedidos';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Crear un registro de historial cuando se finaliza un pedido
     */
    public function crearHistorialPedido($pedidoData)
    {
        try {
            $sql = "INSERT INTO {$this->table} (
                pedido_id, mesa_id, mesa_numero, usuario_id, usuario_nombre, 
                estado_final, total, fecha_creacion, fecha_finalizacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $pedidoData['pedido_id'],
                $pedidoData['mesa_id'],
                $pedidoData['mesa_numero'],
                $pedidoData['usuario_id'],
                $pedidoData['usuario_nombre'],
                $pedidoData['estado_final'],
                $pedidoData['total'],
                $pedidoData['fecha_creacion']
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear historial de pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de pedidos con filtros
     */
    public function getHistorialPedidos($filtros = [])
    {
        try {
            $sql = "SELECT hp.*, 
                           COUNT(hdp.id) as total_items,
                           SUM(hdp.rentabilidad) as rentabilidad_total
                    FROM {$this->table} hp
                    LEFT JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(hp.fecha_finalizacion) >= ?";
                $params[] = $filtros['fecha_inicio'];
            }

            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(hp.fecha_finalizacion) <= ?";
                $params[] = $filtros['fecha_fin'];
            }

            if (!empty($filtros['usuario_id'])) {
                $sql .= " AND hp.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
            }

            if (!empty($filtros['mesa_id'])) {
                $sql .= " AND hp.mesa_id = ?";
                $params[] = $filtros['mesa_id'];
            }

            $sql .= " GROUP BY hp.id ORDER BY hp.fecha_finalizacion DESC";

            if (!empty($filtros['limit'])) {
                $sql .= " LIMIT " . intval($filtros['limit']);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial de pedidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de ventas por período
     */
    public function getEstadisticasVentas($fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        DATE(fecha_finalizacion) as fecha,
                        COUNT(*) as total_pedidos,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_pedido,
                        SUM(hdp.rentabilidad) as rentabilidad_total
                    FROM {$this->table} hp
                    LEFT JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                    WHERE DATE(hp.fecha_finalizacion) BETWEEN ? AND ?
                    GROUP BY DATE(fecha_finalizacion)
                    ORDER BY fecha DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de ventas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener top productos vendidos
     */
    public function getTopProductosVendidos($fechaInicio = null, $fechaFin = null, $limit = 10)
    {
        try {
            $sql = "SELECT 
                        hdp.producto_nombre,
                        SUM(hdp.cantidad) as total_vendido,
                        SUM(hdp.subtotal) as total_ingresos,
                        SUM(hdp.rentabilidad) as rentabilidad_total,
                        AVG(hdp.precio_unitario) as precio_promedio
                    FROM {$this->table} hp
                    JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
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
                      ORDER BY total_vendido DESC 
                      LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener top productos: " . $e->getMessage());
            return [];
        }
    }
    public function getProductosMasVendidos($fechaInicio = null, $fechaFin = null, $limit = 10)
    {
    try {
        $sql = "SELECT 
                    hdp.producto_nombre,
                    SUM(hdp.cantidad) as total_vendido,
                    SUM(hdp.subtotal) as total_ingresos,
                    SUM(hdp.rentabilidad) as rentabilidad_total,
                    AVG(hdp.precio_unitario) as precio_promedio
                FROM {$this->table} hp
                JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
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
                ORDER BY total_vendido DESC 
                LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener productos más vendidos: " . $e->getMessage());
        return [];
    }
    }
    public function getVentasPorDia($fechaInicio, $fechaFin)
    { 
        try {
            $sql = "SELECT 
                        DATE(fecha_finalizacion) as fecha,
                        COUNT(*) as total_pedidos,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_pedido,
                        SUM(hdp.rentabilidad) as rentabilidad_total
                    FROM {$this->table} hp
                    LEFT JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                    WHERE DATE(hp.fecha_finalizacion) = ?
                    GROUP BY DATE(fecha_finalizacion)
                    ORDER BY fecha DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener ventas por día: " . $e->getMessage());
            return [];
        }
    }

    public function getVentasPorMes($año)
    {
        try {
            $sql = "SELECT 
                        MONTH(fecha_finalizacion) as mes,
                        COUNT(*) as total_pedidos,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_pedido,
                        SUM(hdp.rentabilidad) as rentabilidad_total
                    FROM {$this->table} hp
                    LEFT JOIN historial_detalles_pedido hdp ON hp.id = hdp.historial_pedido_id
                    WHERE YEAR(fecha_finalizacion) = ?
                    GROUP BY MONTH(fecha_finalizacion)
                    ORDER BY mes ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$año]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener ventas por mes: " . $e->getMessage());
            return [];
        }
    }

    public function getHorariosPico($fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        HOUR(fecha_finalizacion) as hora,
                        COUNT(*) as total_pedidos,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_pedido
                    FROM {$this->table}
                    WHERE DATE(fecha_finalizacion) BETWEEN ? AND ?
                    GROUP BY HOUR(fecha_finalizacion)
                    ORDER BY total_pedidos DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener horarios pico: " . $e->getMessage());
            return [];
        }
    }
    

    /**
     * Obtener ventas por mesero
     */
    public function getVentasPorMesero($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        hp.usuario_nombre,
                        COUNT(*) as total_pedidos,
                        SUM(hp.total) as total_ventas,
                        AVG(hp.total) as promedio_pedido
                    FROM {$this->table} hp
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

            $sql .= " GROUP BY hp.usuario_id, hp.usuario_nombre 
                      ORDER BY total_ventas DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener ventas por mesero: " . $e->getMessage());
            return [];
        }
    }
    

}
