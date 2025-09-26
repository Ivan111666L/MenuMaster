<?php

namespace App\Models;

use PDO;
use PDOException;

class DetalleCompraProveedorModel extends Model
{
    protected $table = 'detalle_compra_proveedor';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Crear detalle de compra
     */
    public function crear($compraId, $ingredienteId, $cantidadPedida, $precioUnitario, $cantidadRecibida = null)
    {
        try {
            $subtotal = $cantidadPedida * $precioUnitario;
            
            $sql = "INSERT INTO {$this->table} (compra_id, ingrediente_id, cantidad_pedida, cantidad_recibida, precio_unitario, subtotal, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $compraId,
                $ingredienteId,
                $cantidadPedida,
                $cantidadRecibida,
                $precioUnitario,
                $subtotal
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear detalle de compra: " . $e->getMessage());
            return false;
        }
    }

    public function crearDetalle($compraId, $ingredienteId, $cantidadPedida, $precioUnitario, $cantidadRecibida = null)
    {
        return $this->crear($compraId, $ingredienteId, $cantidadPedida, $precioUnitario, $cantidadRecibida);
    }

    /**
     * Obtener detalles de una compra
     */
    public function getDetallesPorCompra($compraId)
    {
        try {
            $sql = "SELECT 
                        dcp.*,
                        i.nombre as ingrediente_nombre,
                        i.unidad_medida,
                        i.stock_actual,
                        i.stock_minimo,
                        (dcp.cantidad_pedida - COALESCE(dcp.cantidad_recibida, 0)) as cantidad_pendiente,
                        CASE 
                            WHEN dcp.cantidad_recibida IS NULL THEN 'pendiente'
                            WHEN dcp.cantidad_recibida = dcp.cantidad_pedida THEN 'completo'
                            WHEN dcp.cantidad_recibida < dcp.cantidad_pedida THEN 'parcial'
                            ELSE 'excedente'
                        END as estado_recepcion
                    FROM {$this->table} dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE dcp.compra_id = ?
                    ORDER BY i.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalles por compra: " . $e->getMessage());
            return [];
        }
    }

    public function getVariacionPrecios($ingredienteId, $fechaInicio, $fechaFin){
        try {
            $sql = "SELECT 
                        dcp.precio_unitario as precio,
                        dcp.created_at as fecha
                    FROM {$this->table} dcp
                    WHERE dcp.ingrediente_id = ?
                    AND dcp.created_at BETWEEN ? AND ?
                    ORDER BY dcp.created_at";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ingredienteId, $fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener variacion de precios: " . $e->getMessage());
            return [];
        }
    }
    

    public function getAnalisisPrecios($fechaInicio, $fechaFin){
        try {
            $sql = "SELECT 
                        i.nombre as ingrediente_nombre,
                        dcp.precio_unitario as precio,
                        dcp.created_at as fecha
                    FROM {$this->table} dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE dcp.created_at BETWEEN ? AND ?
                    ORDER BY dcp.ingrediente_id, dcp.created_at";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener analisis de precios: " . $e->getMessage());
            return [];
        }
    }

    public function getDiscrepanciasRecepcion($fechaInicio, $fechaFin){
        try {
            $sql = "SELECT 
                        dcp.ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        dcp.cantidad_pedida,
                        COALESCE(dcp.cantidad_recibida, 0) as cantidad_recibida,
                        (dcp.cantidad_pedida - COALESCE(dcp.cantidad_recibida, 0)) as diferencia
                    FROM {$this->table} dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE dcp.created_at BETWEEN ? AND ?
                    AND (dcp.cantidad_recibida IS NULL OR dcp.cantidad_recibida < dcp.cantidad_pedida)
                    ORDER BY dcp.ingrediente_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener discrepancias de recepcion: " . $e->getMessage());
            return [];
        }
    }

    public function getEficienciaProveedores($fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.nombre,
                        COUNT(cp.id) as total_compras,
                        SUM(dcp.subtotal) as total_gastado,
                        AVG(dcp.precio_unitario) as precio_promedio,
                        SUM(CASE WHEN cp.estado = 'completada' THEN dcp.subtotal ELSE 0 END) as total_recibido,
                        (SUM(CASE WHEN cp.estado = 'completada' THEN dcp.subtotal ELSE 0 END) / SUM(dcp.subtotal)) * 100 as porcentaje_recibido
                    FROM {$this->table} cp
                    LEFT JOIN detalle_compra_proveedor dcp ON cp.id = dcp.compra_id
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    WHERE cp.fecha_compra BETWEEN ? AND ?
                    GROUP BY p.id, p.nombre
                    ORDER BY porcentaje_recibido DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener eficiencia de proveedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar cantidad recibida
     */
    public function actualizarCantidadRecibida($id, $cantidadRecibida, $observaciones = null)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET cantidad_recibida = ?, observaciones = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cantidadRecibida, $observaciones, $id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar cantidad recibida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar detalle de compra
     */
    public function actualizar($id, $datos)
    {
        try {
            $campos = [];
            $valores = [];

            foreach ($datos as $campo => $valor) {
                if (in_array($campo, ['cantidad_pedida', 'cantidad_recibida', 'precio_unitario', 'observaciones'])) {
                    $campos[] = "$campo = ?";
                    $valores[] = $valor;
                }
            }

            if (empty($campos)) {
                return false;
            }

            // Recalcular subtotal si se actualiza cantidad o precio
            if (in_array('cantidad_pedida', array_keys($datos)) || in_array('precio_unitario', array_keys($datos))) {
                // Obtener valores actuales para recalcular
                $sqlActual = "SELECT cantidad_pedida, precio_unitario FROM {$this->table} WHERE id = ?";
                $stmtActual = $this->db->prepare($sqlActual);
                $stmtActual->execute([$id]);
                $actual = $stmtActual->fetch(PDO::FETCH_ASSOC);

                $nuevaCantidad = $datos['cantidad_pedida'] ?? $actual['cantidad_pedida'];
                $nuevoPrecio = $datos['precio_unitario'] ?? $actual['precio_unitario'];
                
                $campos[] = "subtotal = ?";
                $valores[] = $nuevaCantidad * $nuevoPrecio;
            }

            $campos[] = "updated_at = CURRENT_TIMESTAMP";
            $valores[] = $id;

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($valores);
        } catch (PDOException $e) {
            error_log("Error al actualizar detalle de compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar detalle de compra
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar detalle de compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener resumen de una compra
     */
    public function getResumenCompra($compraId)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_items,
                        SUM(cantidad_pedida) as total_cantidad_pedida,
                        SUM(COALESCE(cantidad_recibida, 0)) as total_cantidad_recibida,
                        SUM(subtotal) as total_subtotal,
                        COUNT(CASE WHEN cantidad_recibida IS NULL THEN 1 END) as items_pendientes,
                        COUNT(CASE WHEN cantidad_recibida = cantidad_pedida THEN 1 END) as items_completos,
                        COUNT(CASE WHEN cantidad_recibida < cantidad_pedida THEN 1 END) as items_parciales,
                        COUNT(CASE WHEN cantidad_recibida > cantidad_pedida THEN 1 END) as items_excedentes
                    FROM {$this->table}
                    WHERE compra_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$compraId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener resumen de compra: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener ingredientes más comprados
     */
    public function getIngredientesMasComprados($fechaInicio = null, $fechaFin = null, $limit = 10)
    {
        try {
            $sql = "SELECT 
                        i.id as ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        i.unidad_medida,
                        COUNT(dcp.id) as total_compras,
                        SUM(dcp.cantidad_pedida) as cantidad_total_pedida,
                        SUM(COALESCE(dcp.cantidad_recibida, 0)) as cantidad_total_recibida,
                        AVG(dcp.precio_unitario) as precio_promedio,
                        SUM(dcp.subtotal) as monto_total,
                        MAX(cp.fecha_compra) as ultima_compra
                    FROM {$this->table} dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    LEFT JOIN compras_proveedor cp ON dcp.compra_id = cp.id
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(cp.fecha_compra) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(cp.fecha_compra) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY i.id, i.nombre, i.unidad_medida
                      ORDER BY cantidad_total_pedida DESC, monto_total DESC
                      LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener ingredientes más comprados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener análisis de variación de precios
     */
    public function getAnalisisVariacionPrecios($ingredienteId = null)
    {
        try {
            $sql = "SELECT 
                        i.id as ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        COUNT(dcp.id) as total_compras,
                        MIN(dcp.precio_unitario) as precio_minimo,
                        MAX(dcp.precio_unitario) as precio_maximo,
                        AVG(dcp.precio_unitario) as precio_promedio,
                        STDDEV(dcp.precio_unitario) as desviacion_precio,
                        (MAX(dcp.precio_unitario) - MIN(dcp.precio_unitario)) as rango_precio,
                        ROUND(((MAX(dcp.precio_unitario) - MIN(dcp.precio_unitario)) / AVG(dcp.precio_unitario)) * 100, 2) as variacion_porcentual
                    FROM {$this->table} dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    LEFT JOIN compras_proveedor cp ON dcp.compra_id = cp.id
                    WHERE cp.estado = 'completada'";

            $params = [];

            if ($ingredienteId) {
                $sql .= " AND i.id = ?";
                $params[] = $ingredienteId;
            }

            $sql .= " GROUP BY i.id, i.nombre
                      HAVING total_compras > 1
                      ORDER BY variacion_porcentual DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener análisis de variación de precios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener discrepancias en recepciones
     */
    public function getDiscrepanciasRecepciones($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        dcp.id,
                        cp.id as compra_id,
                        cp.fecha_compra,
                        p.nombre as proveedor_nombre,
                        i.nombre as ingrediente_nombre,
                        dcp.cantidad_pedida,
                        dcp.cantidad_recibida,
                        (dcp.cantidad_pedida - COALESCE(dcp.cantidad_recibida, 0)) as diferencia,
                        ROUND(((COALESCE(dcp.cantidad_recibida, 0) / dcp.cantidad_pedida) * 100), 2) as porcentaje_recibido,
                        dcp.precio_unitario,
                        (dcp.cantidad_pedida - COALESCE(dcp.cantidad_recibida, 0)) * dcp.precio_unitario as valor_diferencia,
                        dcp.observaciones
                    FROM {$this->table} dcp
                    LEFT JOIN compras_proveedor cp ON dcp.compra_id = cp.id
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE (dcp.cantidad_recibida IS NULL OR dcp.cantidad_recibida != dcp.cantidad_pedida)
                    AND cp.estado = 'completada'";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(cp.fecha_compra) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(cp.fecha_compra) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " ORDER BY ABS(diferencia) DESC, cp.fecha_compra DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener discrepancias en recepciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de eficiencia de proveedores
     */
    public function getEstadisticasEficienciaProveedores()
    {
        try {
            $sql = "SELECT 
                        p.id as proveedor_id,
                        p.nombre as proveedor_nombre,
                        COUNT(dcp.id) as total_items_pedidos,
                        COUNT(CASE WHEN dcp.cantidad_recibida = dcp.cantidad_pedida THEN 1 END) as items_completos,
                        COUNT(CASE WHEN dcp.cantidad_recibida < dcp.cantidad_pedida THEN 1 END) as items_incompletos,
                        COUNT(CASE WHEN dcp.cantidad_recibida > dcp.cantidad_pedida THEN 1 END) as items_excedentes,
                        COUNT(CASE WHEN dcp.cantidad_recibida IS NULL THEN 1 END) as items_no_recibidos,
                        ROUND((COUNT(CASE WHEN dcp.cantidad_recibida = dcp.cantidad_pedida THEN 1 END) / COUNT(dcp.id)) * 100, 2) as porcentaje_eficiencia,
                        SUM(dcp.cantidad_pedida) as cantidad_total_pedida,
                        SUM(COALESCE(dcp.cantidad_recibida, 0)) as cantidad_total_recibida,
                        ROUND((SUM(COALESCE(dcp.cantidad_recibida, 0)) / SUM(dcp.cantidad_pedida)) * 100, 2) as porcentaje_cumplimiento
                    FROM proveedores p
                    LEFT JOIN compras_proveedor cp ON p.id = cp.proveedor_id
                    LEFT JOIN {$this->table} dcp ON cp.id = dcp.compra_id
                    WHERE cp.estado = 'completada' AND dcp.id IS NOT NULL
                    GROUP BY p.id, p.nombre
                    ORDER BY porcentaje_eficiencia DESC, porcentaje_cumplimiento DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de eficiencia de proveedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener historial de precios de un ingrediente
     */
    public function getHistorialPreciosIngrediente($ingredienteId, $limit = 20, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        cp.fecha_compra,
                        p.nombre as proveedor_nombre,
                        dcp.precio_unitario,
                        dcp.cantidad_pedida,
                        dcp.cantidad_recibida,
                        dcp.subtotal,
                        LAG(dcp.precio_unitario) OVER (ORDER BY cp.fecha_compra) as precio_anterior,
                        (dcp.precio_unitario - LAG(dcp.precio_unitario) OVER (ORDER BY cp.fecha_compra)) as diferencia_precio,
                        ROUND(((dcp.precio_unitario - LAG(dcp.precio_unitario) OVER (ORDER BY cp.fecha_compra)) / LAG(dcp.precio_unitario) OVER (ORDER BY cp.fecha_compra)) * 100, 2) as porcentaje_cambio
                    FROM {$this->table} dcp
                    LEFT JOIN compras_proveedor cp ON dcp.compra_id = cp.id
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    WHERE dcp.ingrediente_id = ? AND cp.estado = 'completada'
                    ORDER BY cp.fecha_compra DESC
                    LIMIT ?";

            $params = [$ingredienteId, $limit];

            if ($fechaInicio) {
                $sql .= " AND DATE(cp.fecha_compra) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(cp.fecha_compra) <= ?";
                $params[] = $fechaFin;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial de precios: " . $e->getMessage());
            return [];
        }
    }

    public function actualizarDetalle($detalleId, $data, $precioUnitario, $cantidadRecibida)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                        cantidad_recibida = ?,
                        precio_unitario = ?,
                        subtotal = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $cantidadRecibida,
                $precioUnitario,
                $precioUnitario * $cantidadRecibida,
                $detalleId
            ]);

            if ($result) {
                return $result;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al actualizar detalle de compra: " . $e->getMessage());
            return false;
        }
    }
    

    /**
     * Crear múltiples detalles de compra
     */
    public function crearMultiples($compraId, $detalles)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO {$this->table} (compra_id, ingrediente_id, cantidad_pedida, cantidad_recibida, precio_unitario, subtotal, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            $totalSubtotal = 0;

            foreach ($detalles as $detalle) {
                $subtotal = $detalle['cantidad_pedida'] * $detalle['precio_unitario'];
                $totalSubtotal += $subtotal;

                $result = $stmt->execute([
                    $compraId,
                    $detalle['ingrediente_id'],
                    $detalle['cantidad_pedida'],
                    $detalle['cantidad_recibida'] ?? null,
                    $detalle['precio_unitario'],
                    $subtotal
                ]);

                if (!$result) {
                    $this->db->rollBack();
                    return false;
                }
            }

            // Actualizar el total de la compra
            $sqlUpdateTotal = "UPDATE compras_proveedor SET total = ? WHERE id = ?";
            $stmtUpdateTotal = $this->db->prepare($sqlUpdateTotal);
            $stmtUpdateTotal->execute([$totalSubtotal, $compraId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al crear múltiples detalles de compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener proyección de costos
     */
    public function getProyeccionCostos($ingredienteId, $cantidadNecesaria)
    {
        try {
            // Obtener precios históricos para calcular tendencia
            $sql = "SELECT 
                        AVG(precio_unitario) as precio_promedio,
                        MIN(precio_unitario) as precio_minimo,
                        MAX(precio_unitario) as precio_maximo,
                        COUNT(*) as total_compras,
                        STDDEV(precio_unitario) as desviacion_precio
                    FROM {$this->table} dcp
                    LEFT JOIN compras_proveedor cp ON dcp.compra_id = cp.id
                    WHERE dcp.ingrediente_id = ? 
                    AND cp.estado = 'completada'
                    AND cp.fecha_compra >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ingredienteId]);
            $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($estadisticas && $estadisticas['total_compras'] > 0) {
                return [
                    'cantidad_necesaria' => $cantidadNecesaria,
                    'costo_promedio' => $cantidadNecesaria * $estadisticas['precio_promedio'],
                    'costo_minimo' => $cantidadNecesaria * $estadisticas['precio_minimo'],
                    'costo_maximo' => $cantidadNecesaria * $estadisticas['precio_maximo'],
                    'precio_promedio' => $estadisticas['precio_promedio'],
                    'precio_minimo' => $estadisticas['precio_minimo'],
                    'precio_maximo' => $estadisticas['precio_maximo'],
                    'desviacion_precio' => $estadisticas['desviacion_precio'],
                    'total_compras_historicas' => $estadisticas['total_compras']
                ];
            }

            return [];
        } catch (PDOException $e) {
            error_log("Error al obtener proyección de costos: " . $e->getMessage());
            return [];
        }
    }
    public function getReporteCompleto($compraId)
    {
        try {
            $sql = "SELECT 
                        dcp.id as detalle_id,
                        i.nombre as ingrediente_nombre,
                        dcp.cantidad_pedida,
                        dcp.cantidad_recibida,
                        dcp.precio_unitario,
                        dcp.subtotal
                    FROM {$this->table} dcp
                    JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE dcp.compra_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reporte completo: " . $e->getMessage());
            return [];
        }
    }
}
