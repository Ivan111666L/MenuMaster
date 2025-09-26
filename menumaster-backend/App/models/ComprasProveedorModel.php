<?php

namespace App\Models;

use PDO;
use PDOException;

class ComprasProveedorModel extends Model
{
    protected $table = 'compras_proveedor';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Crear una nueva compra a proveedor
     */
    public function crear($proveedorId, $usuarioId, $fechaCompra, $total, $estado = 'pendiente', $observaciones = null)
    {
        try {
            $sql = "INSERT INTO {$this->table} (proveedor_id, usuario_id, fecha_compra, total, estado, observaciones, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $proveedorId,
                $usuarioId,
                $fechaCompra,
                $total,
                $estado,
                $observaciones
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear compra a proveedor: " . $e->getMessage());
            return false;
        }
    }

    public function crearCompra($proveedorId, $usuarioId, $fechaEntregaEsperada, $observaciones)
    {
        try {
            $sql = "INSERT INTO {$this->table} (proveedor_id, usuario_id, fecha_entrega_esperada, observaciones, created_at) 
                    VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $proveedorId,
                $usuarioId,
                $fechaEntregaEsperada,
                $observaciones
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear compra a proveedor: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarTotal($compraId, $totalCompra)
    {
        try {
            $sql = "UPDATE {$this->table} SET total = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$totalCompra, $compraId]);
        } catch (PDOException $e) {
            error_log("Error al actualizar total de compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener compras con filtros
     */
    public function getCompras($filtros = [])
    {
        try {
            $sql = "SELECT 
                        cp.*,
                        p.nombre as proveedor_nombre,
                        p.telefono as proveedor_telefono,
                        p.email as proveedor_email,
                        u.nombre as usuario_nombre,
                        COUNT(dcp.id) as total_items
                    FROM {$this->table} cp
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    LEFT JOIN usuarios u ON cp.usuario_id = u.id
                    LEFT JOIN detalle_compra_proveedor dcp ON cp.id = dcp.compra_id
                    WHERE 1=1";

            $params = [];

            if (!empty($filtros['proveedor_id'])) {
                $sql .= " AND cp.proveedor_id = ?";
                $params[] = $filtros['proveedor_id'];
            }

            if (!empty($filtros['estado'])) {
                $sql .= " AND cp.estado = ?";
                $params[] = $filtros['estado'];
            }

            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(cp.fecha_compra) >= ?";
                $params[] = $filtros['fecha_inicio'];
            }

            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(cp.fecha_compra) <= ?";
                $params[] = $filtros['fecha_fin'];
            }

            if (!empty($filtros['usuario_id'])) {
                $sql .= " AND cp.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
            }

            $sql .= " GROUP BY cp.id, p.nombre, p.telefono, p.email, u.nombre
                      ORDER BY cp.fecha_compra DESC";

            if (!empty($filtros['limit'])) {
                $sql .= " LIMIT " . intval($filtros['limit']);
                if (!empty($filtros['offset'])) {
                    $sql .= " OFFSET " . intval($filtros['offset']);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener compras: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una compra por ID con detalles
     */
    public function getCompraPorId($id)
    {
        try {
            $sql = "SELECT 
                        cp.*,
                        p.nombre as proveedor_nombre,
                        p.telefono as proveedor_telefono,
                        p.email as proveedor_email,
                        p.direccion as proveedor_direccion,
                        u.nombre as usuario_nombre
                    FROM {$this->table} cp
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    LEFT JOIN usuarios u ON cp.usuario_id = u.id
                    WHERE cp.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener compra por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de compra
     */
    public function actualizarEstado($id, $estado, $observaciones = null)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET estado = ?, observaciones = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $observaciones, $id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar compra
     */
    public function actualizar($id, $datos)
    {
        try {
            $campos = [];
            $valores = [];

            foreach ($datos as $campo => $valor) {
                if (in_array($campo, ['proveedor_id', 'fecha_compra', 'total', 'estado', 'observaciones'])) {
                    $campos[] = "$campo = ?";
                    $valores[] = $valor;
                }
            }

            if (empty($campos)) {
                return false;
            }

            $campos[] = "updated_at = CURRENT_TIMESTAMP";
            $valores[] = $id;

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($valores);
        } catch (PDOException $e) {
            error_log("Error al actualizar compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar compra
     */
    public function eliminar($id)
    {
        try {
            // Primero eliminar los detalles
            $sqlDetalles = "DELETE FROM detalle_compra_proveedor WHERE compra_id = ?";
            $stmtDetalles = $this->db->prepare($sqlDetalles);
            $stmtDetalles->execute([$id]);

            // Luego eliminar la compra
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de compras
     */
    public function getEstadisticasCompras($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_compras,
                        SUM(total) as monto_total,
                        AVG(total) as monto_promedio,
                        COUNT(CASE WHEN estado = 'completada' THEN 1 END) as compras_completadas,
                        COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as compras_pendientes,
                        COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as compras_canceladas,
                        COUNT(DISTINCT proveedor_id) as proveedores_utilizados
                    FROM {$this->table}
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(fecha_compra) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(fecha_compra) <= ?";
                $params[] = $fechaFin;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de compras: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener compras por proveedor
     */
    public function getComprasPorProveedor($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        p.id as proveedor_id,
                        p.nombre as proveedor_nombre,
                        COUNT(cp.id) as total_compras,
                        SUM(cp.total) as monto_total,
                        AVG(cp.total) as monto_promedio,
                        MAX(cp.fecha_compra) as ultima_compra,
                        COUNT(CASE WHEN cp.estado = 'completada' THEN 1 END) as compras_completadas
                    FROM proveedores p
                    LEFT JOIN {$this->table} cp ON p.id = cp.proveedor_id
                    WHERE cp.id IS NOT NULL";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(cp.fecha_compra) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(cp.fecha_compra) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY p.id, p.nombre
                      ORDER BY monto_total DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener compras por proveedor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener compras por mes
     */
    public function getComprasPorMes($año = null)
    {
        try {
            $sql = "SELECT 
                        YEAR(fecha_compra) as año,
                        MONTH(fecha_compra) as mes,
                        MONTHNAME(fecha_compra) as nombre_mes,
                        COUNT(*) as total_compras,
                        SUM(total) as monto_total,
                        AVG(total) as monto_promedio
                    FROM {$this->table}
                    WHERE 1=1";

            $params = [];

            if ($año) {
                $sql .= " AND YEAR(fecha_compra) = ?";
                $params[] = $año;
            }

            $sql .= " GROUP BY YEAR(fecha_compra), MONTH(fecha_compra)
                      ORDER BY año DESC, mes DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener compras por mes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proveedores más utilizados
     */
    public function getProveedoresMasUtilizados($limit = 10, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        p.id as proveedor_id,
                        p.nombre as proveedor_nombre,
                        p.telefono,
                        p.email,
                        COUNT(cp.id) as total_compras,
                        SUM(cp.total) as monto_total,
                        AVG(cp.total) as monto_promedio,
                        MAX(cp.fecha_compra) as ultima_compra,
                        DATEDIFF(CURDATE(), MAX(cp.fecha_compra)) as dias_ultima_compra
                    FROM proveedores p
                    LEFT JOIN {$this->table} cp ON p.id = cp.proveedor_id
                    WHERE cp.id IS NOT NULL";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(cp.fecha_compra) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(cp.fecha_compra) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY p.id, p.nombre, p.telefono, p.email
                      ORDER BY total_compras DESC, monto_total DESC
                      LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proveedores más utilizados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener compras pendientes
     */
    public function getComprasPendientes()
    {
        try {
            $sql = "SELECT 
                        cp.*,
                        p.nombre as proveedor_nombre,
                        p.telefono as proveedor_telefono,
                        p.email as proveedor_email,
                        u.nombre as usuario_nombre,
                        DATEDIFF(CURDATE(), cp.fecha_compra) as dias_pendiente
                    FROM {$this->table} cp
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    LEFT JOIN usuarios u ON cp.usuario_id = u.id
                    WHERE cp.estado = 'pendiente'
                    ORDER BY cp.fecha_compra ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener compras pendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Marcar compra como recibida
     */
    public function marcarComoRecibida($id, $usuarioId, $observaciones = null,$detallesRecepcion = [])
    {
        try {
            $this->db->beginTransaction();

            // Actualizar estado de la compra
            $sql = "UPDATE {$this->table} 
                    SET estado = 'completada', 
                        fecha_recepcion = CURRENT_TIMESTAMP,
                        usuario_recepcion_id = ?,
                        observaciones = ?,
                        detalles_recepcion = ?
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$usuarioId, $observaciones, $detallesRecepcion, $id]);

            if ($result) {
                // Actualizar stock de ingredientes basado en los detalles de la compra
                $sqlDetalles = "SELECT ingrediente_id, cantidad_recibida 
                               FROM detalle_compra_proveedor 
                               WHERE compra_id = ?";
                $stmtDetalles = $this->db->prepare($sqlDetalles);
                $stmtDetalles->execute([$id]);
                $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

                foreach ($detalles as $detalle) {
                    $sqlUpdateStock = "UPDATE ingredientes 
                                      SET stock_actual = stock_actual + ? 
                                      WHERE id = ?";
                    $stmtUpdateStock = $this->db->prepare($sqlUpdateStock);
                    $stmtUpdateStock->execute([$detalle['cantidad_recibida'], $detalle['ingrediente_id']]);
                }

                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al marcar compra como recibida: " . $e->getMessage());
            return false;
        }
    }

    public function generarOrdenAutomatica($compraId)
    {
        try {
            $sql = "SELECT 
                        dcp.ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        dcp.cantidad_pedida,
                        dcp.precio_unitario,
                        (dcp.cantidad_pedida - COALESCE(dcp.cantidad_recibida, 0)) as cantidad_pendiente
                    FROM {$this->table} dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE dcp.compra_id = ?
                    ORDER BY i.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al generar orden automatica: " . $e->getMessage());
            return [];
        }
    }

    public function getAnalisisProveedores($fechaInicio, $fechaFin){
        try {
            $sql = "SELECT 
                        p.id,
                        p.nombre,
                        COUNT(cp.id) as total_compras,
                        SUM(dcp.subtotal) as total_gastado,
                        AVG(dcp.precio_unitario) as precio_promedio
                    FROM {$this->table} cp
                    LEFT JOIN detalle_compra_proveedor dcp ON cp.id = dcp.compra_id
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    WHERE cp.fecha_compra BETWEEN ? AND ?
                    GROUP BY p.id, p.nombre
                    ORDER BY total_gastado DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener analisis de proveedores: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarCompra($idCompraId)
    {
        try {
            $this->db->beginTransaction();

            // Eliminar detalles de la compra
            $sqlDetalles = "DELETE FROM detalle_compra_proveedor WHERE compra_id = ?";
            $stmtDetalles = $this->db->prepare($sqlDetalles);
            $stmtDetalles->execute([$idCompraId]);
            

            // Eliminar la compra principal
            $sqlCompra = "DELETE FROM {$this->table} WHERE id = ?";
            $stmtCompra = $this->db->prepare($sqlCompra);
            $result = $stmtCompra->execute([$idCompraId]);

            if ($result) {
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al eliminar compra: " . $e->getMessage());
            return false;
        }
    }


    public function getSugerenciasCompra($compraId)
    {
        try {
            $sql = "SELECT 
                        dcp.ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        dcp.cantidad_pedida,
                        dcp.cantidad_recibida,
                        dcp.precio_unitario,
                        (dcp.cantidad_pedida - COALESCE(dcp.cantidad_recibida, 0)) as cantidad_pendiente
                    FROM detalle_compra_proveedor dcp
                    LEFT JOIN ingredientes i ON dcp.ingrediente_id = i.id
                    WHERE dcp.compra_id = ?
                    ORDER BY i.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalles de compra: " . $e->getMessage());
            return [];
        }
    }
    


    /**
     * Obtener historial de compras de un ingrediente
     */
    public function getHistorialComprasIngrediente($ingredienteId, $limit = 20)
    {
        try {
            $sql = "SELECT 
                        cp.id as compra_id,
                        cp.fecha_compra,
                        cp.estado,
                        p.nombre as proveedor_nombre,
                        dcp.cantidad_pedida,
                        dcp.cantidad_recibida,
                        dcp.precio_unitario,
                        dcp.subtotal,
                        u.nombre as usuario_nombre
                    FROM {$this->table} cp
                    LEFT JOIN detalle_compra_proveedor dcp ON cp.id = dcp.compra_id
                    LEFT JOIN proveedores p ON cp.proveedor_id = p.id
                    LEFT JOIN usuarios u ON cp.usuario_id = u.id
                    WHERE dcp.ingrediente_id = ?
                    ORDER BY cp.fecha_compra DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ingredienteId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial de compras de ingrediente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generar orden de compra automática basada en stock mínimo
     */
    public function generarOrdenCompraAutomatica($usuarioId)
    {
        try {
            // Obtener ingredientes que necesitan reposición
            $sqlIngredientes = "SELECT 
                                    i.id,
                                    i.nombre,
                                    i.stock_actual,
                                    i.stock_minimo,
                                    (i.stock_minimo * 2) as cantidad_sugerida
                                FROM ingredientes i
                                WHERE i.stock_actual <= i.stock_minimo
                                AND i.estado = 'activo'";

            $stmtIngredientes = $this->db->prepare($sqlIngredientes);
            $stmtIngredientes->execute();
            $ingredientes = $stmtIngredientes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ingredientes)) {
                return ['mensaje' => 'No hay ingredientes que requieran reposición'];
            }

            $ordenesGeneradas = [];

            // Agrupar por proveedor con mejor precio
            foreach ($ingredientes as $ingrediente) {
                $sqlMejorProveedor = "SELECT 
                                        pi.proveedor_id,
                                        pi.precio_unitario,
                                        p.nombre as proveedor_nombre
                                      FROM proveedor_ingrediente pi
                                      LEFT JOIN proveedores p ON pi.proveedor_id = p.id
                                      WHERE pi.ingrediente_id = ? AND p.estado = 'activo'
                                      ORDER BY pi.precio_unitario ASC
                                      LIMIT 1";

                $stmtProveedor = $this->db->prepare($sqlMejorProveedor);
                $stmtProveedor->execute([$ingrediente['id']]);
                $proveedor = $stmtProveedor->fetch(PDO::FETCH_ASSOC);

                if ($proveedor) {
                    $proveedorId = $proveedor['proveedor_id'];
                    
                    if (!isset($ordenesGeneradas[$proveedorId])) {
                        $ordenesGeneradas[$proveedorId] = [
                            'proveedor_nombre' => $proveedor['proveedor_nombre'],
                            'ingredientes' => [],
                            'total' => 0
                        ];
                    }

                    $subtotal = $ingrediente['cantidad_sugerida'] * $proveedor['precio_unitario'];
                    $ordenesGeneradas[$proveedorId]['ingredientes'][] = [
                        'ingrediente_id' => $ingrediente['id'],
                        'ingrediente_nombre' => $ingrediente['nombre'],
                        'cantidad' => $ingrediente['cantidad_sugerida'],
                        'precio_unitario' => $proveedor['precio_unitario'],
                        'subtotal' => $subtotal
                    ];
                    $ordenesGeneradas[$proveedorId]['total'] += $subtotal;
                }
            }

            return [
                'ordenes_sugeridas' => $ordenesGeneradas,
                'total_ordenes' => count($ordenesGeneradas),
                'total_ingredientes' => count($ingredientes)
            ];
        } catch (PDOException $e) {
            error_log("Error al generar orden de compra automática: " . $e->getMessage());
            return [];
        }
    }
}
