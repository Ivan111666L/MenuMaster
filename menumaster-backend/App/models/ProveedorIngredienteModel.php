<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;

class ProveedorIngredienteModel extends Model
{
    protected $table = 'proveedor_ingrediente';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Crear relación proveedor-ingrediente
     */
    public function crear($proveedorId, $ingredienteId, $precioUnitario, $tiempoEntrega = null, $cantidadMinima = null)
    {
        try {
            $sql = "INSERT INTO {$this->table} (proveedor_id, ingrediente_id, precio_unitario, tiempo_entrega_dias, cantidad_minima_pedido, created_at) 
                    VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $proveedorId,
                $ingredienteId,
                $precioUnitario,
                $tiempoEntrega,
                $cantidadMinima
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear relación proveedor-ingrediente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener ingredientes de un proveedor
     */
    public function getIngredientesPorProveedor($proveedorId)
    {
        try {
            $sql = "SELECT 
                        pi.*,
                        i.nombre as ingrediente_nombre,
                        i.unidad_medida,
                        i.stock_actual,
                        i.stock_minimo,
                        i.precio_unitario as precio_actual_ingrediente,
                        p.nombre as proveedor_nombre,
                        p.telefono as proveedor_telefono,
                        p.email as proveedor_email,
                        (pi.precio_unitario - i.precio_unitario) as diferencia_precio
                    FROM {$this->table} pi
                    LEFT JOIN ingredientes i ON pi.ingrediente_id = i.id
                    LEFT JOIN proveedores p ON pi.proveedor_id = p.id
                    WHERE pi.proveedor_id = ?
                    ORDER BY i.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$proveedorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener ingredientes por proveedor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proveedores de un ingrediente
     */
    public function getProveedoresPorIngrediente($ingredienteId)
    {
        try {
            $sql = "SELECT 
                        pi.*,
                        p.nombre as proveedor_nombre,
                        p.telefono as proveedor_telefono,
                        p.email as proveedor_email,
                        p.direccion as proveedor_direccion,
                        p.estado as proveedor_estado,
                        i.nombre as ingrediente_nombre,
                        i.precio_unitario as precio_actual_ingrediente,
                        (pi.precio_unitario - i.precio_unitario) as diferencia_precio,
                        CASE 
                            WHEN pi.precio_unitario < i.precio_unitario THEN 'mejor_precio'
                            WHEN pi.precio_unitario = i.precio_unitario THEN 'mismo_precio'
                            ELSE 'precio_mayor'
                        END as comparacion_precio
                    FROM {$this->table} pi
                    LEFT JOIN proveedores p ON pi.proveedor_id = p.id
                    LEFT JOIN ingredientes i ON pi.ingrediente_id = i.id
                    WHERE pi.ingrediente_id = ?
                    ORDER BY pi.precio_unitario ASC, pi.tiempo_entrega_dias ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ingredienteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proveedores por ingrediente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener mejor proveedor para un ingrediente
     */
    public function getMejorProveedor($ingredienteId, $criterio = 'precio')
    {
        try {
            $orderBy = 'pi.precio_unitario ASC';
            
            switch ($criterio) {
                case 'tiempo':
                    $orderBy = 'pi.tiempo_entrega_dias ASC, pi.precio_unitario ASC';
                    break;
                case 'cantidad':
                    $orderBy = 'pi.cantidad_minima_pedido ASC, pi.precio_unitario ASC';
                    break;
                case 'precio':
                default:
                    $orderBy = 'pi.precio_unitario ASC, pi.tiempo_entrega_dias ASC';
                    break;
            }

            $sql = "SELECT 
                        pi.*,
                        p.nombre as proveedor_nombre,
                        p.telefono as proveedor_telefono,
                        p.email as proveedor_email,
                        p.estado as proveedor_estado,
                        i.nombre as ingrediente_nombre
                    FROM {$this->table} pi
                    LEFT JOIN proveedores p ON pi.proveedor_id = p.id
                    LEFT JOIN ingredientes i ON pi.ingrediente_id = i.id
                    WHERE pi.ingrediente_id = ? AND p.estado = 'activo'
                    ORDER BY {$orderBy}
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ingredienteId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener mejor proveedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar relación proveedor-ingrediente
     */
    public function actualizar($id, $datos)
    {
        try {
            $campos = [];
            $valores = [];

            foreach ($datos as $campo => $valor) {
                if (in_array($campo, ['precio_unitario', 'tiempo_entrega_dias', 'cantidad_minima_pedido'])) {
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
            error_log("Error al actualizar relación proveedor-ingrediente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar relación proveedor-ingrediente
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar relación proveedor-ingrediente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener análisis de precios por ingrediente
     */
    public function getAnalisisPrecios($ingredienteId = null)
    {
        try {
            $sql = "SELECT 
                        i.id as ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        i.precio_unitario as precio_actual,
                        COUNT(pi.proveedor_id) as total_proveedores,
                        MIN(pi.precio_unitario) as precio_minimo,
                        MAX(pi.precio_unitario) as precio_maximo,
                        AVG(pi.precio_unitario) as precio_promedio,
                        (i.precio_unitario - MIN(pi.precio_unitario)) as ahorro_potencial,
                        ROUND(((i.precio_unitario - MIN(pi.precio_unitario)) / i.precio_unitario) * 100, 2) as porcentaje_ahorro
                    FROM ingredientes i
                    LEFT JOIN {$this->table} pi ON i.id = pi.ingrediente_id
                    LEFT JOIN proveedores p ON pi.proveedor_id = p.id
                    WHERE p.estado = 'activo'";

            $params = [];
            if ($ingredienteId) {
                $sql .= " AND i.id = ?";
                $params[] = $ingredienteId;
            }

            $sql .= " GROUP BY i.id, i.nombre, i.precio_unitario
                      HAVING total_proveedores > 0
                      ORDER BY porcentaje_ahorro DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener análisis de precios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proveedores con mejores precios
     */
    public function getProveedoresMejoresPrecios($limit = 10)
    {
        try {
            $sql = "SELECT 
                        p.id as proveedor_id,
                        p.nombre as proveedor_nombre,
                        p.telefono,
                        p.email,
                        COUNT(pi.ingrediente_id) as total_ingredientes,
                        AVG(pi.precio_unitario) as precio_promedio,
                        AVG(pi.tiempo_entrega_dias) as tiempo_entrega_promedio,
                        COUNT(CASE WHEN pi.precio_unitario < i.precio_unitario THEN 1 END) as ingredientes_mejor_precio,
                        ROUND((COUNT(CASE WHEN pi.precio_unitario < i.precio_unitario THEN 1 END) / COUNT(pi.ingrediente_id)) * 100, 2) as porcentaje_mejor_precio
                    FROM proveedores p
                    LEFT JOIN {$this->table} pi ON p.id = pi.proveedor_id
                    LEFT JOIN ingredientes i ON pi.ingrediente_id = i.id
                    WHERE p.estado = 'activo' AND pi.ingrediente_id IS NOT NULL
                    GROUP BY p.id, p.nombre, p.telefono, p.email
                    ORDER BY porcentaje_mejor_precio DESC, ingredientes_mejor_precio DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proveedores con mejores precios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generar reporte de comparación de precios
     */
    public function getReporteComparacionPrecios()
    {
        try {
            $sql = "SELECT 
                        i.nombre as ingrediente,
                        i.precio_unitario as precio_actual,
                        i.unidad_medida,
                        GROUP_CONCAT(
                            CONCAT(p.nombre, ': $', pi.precio_unitario, ' (', pi.tiempo_entrega_dias, ' días)')
                            ORDER BY pi.precio_unitario ASC
                            SEPARATOR ' | '
                        ) as ofertas_proveedores,
                        MIN(pi.precio_unitario) as mejor_precio,
                        (SELECT p2.nombre FROM proveedores p2 
                         JOIN {$this->table} pi2 ON p2.id = pi2.proveedor_id 
                         WHERE pi2.ingrediente_id = i.id AND pi2.precio_unitario = MIN(pi.precio_unitario)
                         AND p2.estado = 'activo' LIMIT 1) as mejor_proveedor,
                        (i.precio_unitario - MIN(pi.precio_unitario)) as ahorro_potencial
                    FROM ingredientes i
                    LEFT JOIN {$this->table} pi ON i.id = pi.ingrediente_id
                    LEFT JOIN proveedores p ON pi.proveedor_id = p.id
                    WHERE p.estado = 'activo'
                    GROUP BY i.id, i.nombre, i.precio_unitario, i.unidad_medida
                    HAVING COUNT(pi.proveedor_id) > 0
                    ORDER BY ahorro_potencial DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al generar reporte de comparación: " . $e->getMessage());
            return [];
        }
    }

    public function crearRelacion($proveedorId, $ingredienteId, $precioUnitario, $tiempoEntrega = null, $cantidadMinima = null)
    {
        return $this->crear($proveedorId, $ingredienteId, $precioUnitario, $tiempoEntrega, $cantidadMinima);
    }
       
    public function getMejoresProveedores($ingredienteId)
    {
        try {
            $sql = "SELECT 
                        p.id as proveedor_id,
                        p.nombre as proveedor_nombre,
                        p.telefono,
                        p.email,
                        pi.precio_unitario,
                        pi.tiempo_entrega_dias,
                        pi.cantidad_minima
                    FROM {$this->table} pi
                    JOIN proveedores p ON pi.proveedor_id = p.id
                    WHERE pi.ingrediente_id = ? AND p.estado = 'activo'
                    ORDER BY pi.precio_unitario ASC, pi.tiempo_entrega_dias ASC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ingredienteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener mejores proveedores: " . $e->getMessage());
            return [];
        }
    }



    /**
     * Obtener ingredientes sin proveedores alternativos
     */
    public function getIngredientesSinProveedoresAlternativos()
    {
        try {
            $sql = "SELECT 
                        i.id as ingrediente_id,
                        i.nombre as ingrediente_nombre,
                        i.precio_unitario,
                        i.stock_actual,
                        i.stock_minimo,
                        COUNT(pi.proveedor_id) as total_proveedores
                    FROM ingredientes i
                    LEFT JOIN {$this->table} pi ON i.id = pi.ingrediente_id
                    LEFT JOIN proveedores p ON pi.proveedor_id = p.id AND p.estado = 'activo'
                    GROUP BY i.id, i.nombre, i.precio_unitario, i.stock_actual, i.stock_minimo
                    HAVING total_proveedores <= 1
                    ORDER BY i.stock_actual ASC, total_proveedores ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener ingredientes sin proveedores alternativos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular ahorro potencial total
     */
    public function calcularAhorroPotencialTotal()
    {
        try {
            $sql = "SELECT 
                        SUM(i.precio_unitario - pi.precio_minimo) as ahorro_total,
                        COUNT(DISTINCT i.id) as ingredientes_con_ahorro,
                        AVG(i.precio_unitario - pi.precio_minimo) as ahorro_promedio
                    FROM ingredientes i
                    JOIN (
                        SELECT 
                            ingrediente_id,
                            MIN(precio_unitario) as precio_minimo
                        FROM {$this->table} pi2
                        JOIN proveedores p2 ON pi2.proveedor_id = p2.id
                        WHERE p2.estado = 'activo'
                        GROUP BY ingrediente_id
                    ) pi ON i.id = pi.ingrediente_id
                    WHERE i.precio_unitario > pi.precio_minimo";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al calcular ahorro potencial total: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener sugerencias de compra
     */
    public function getSugerenciasCompra($ingredientesNecesarios = [])
    {
        try {
            $sugerencias = [];

            if (empty($ingredientesNecesarios)) {
                // Si no se especifican ingredientes, buscar los que están bajo stock mínimo
                $sql = "SELECT id, nombre, stock_actual, stock_minimo 
                        FROM ingredientes 
                        WHERE stock_actual <= stock_minimo";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $ingredientesNecesarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach ($ingredientesNecesarios as $ingrediente) {
                $ingredienteId = is_array($ingrediente) ? $ingrediente['id'] : $ingrediente;
                $mejorProveedor = $this->getMejorProveedor($ingredienteId, 'precio');
                
                if ($mejorProveedor) {
                    $sugerencias[] = [
                        'ingrediente_id' => $ingredienteId,
                        'ingrediente_nombre' => $mejorProveedor['ingrediente_nombre'],
                        'proveedor_recomendado' => $mejorProveedor['proveedor_nombre'],
                        'precio_unitario' => $mejorProveedor['precio_unitario'],
                        'tiempo_entrega' => $mejorProveedor['tiempo_entrega_dias'],
                        'cantidad_minima' => $mejorProveedor['cantidad_minima_pedido'],
                        'contacto' => [
                            'telefono' => $mejorProveedor['proveedor_telefono'],
                            'email' => $mejorProveedor['proveedor_email']
                        ]
                    ];
                }
            }

            return $sugerencias;
        } catch (Exception $e) {
            error_log("Error al obtener sugerencias de compra: " . $e->getMessage());
            return [];
        }
    }
}
