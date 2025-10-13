<?php
namespace App\Models;

use Exception;

class PedidoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Lista pedidos (resumen) con opción de filtrar por estado.
     * Devuelve campos mínimos para la UI de facturación: id, mesa_numero, estado/estado_id, fecha.
     * @param string|null $estado Lista de estados separados por coma (ej: "servido,pendiente")
     * @return array|false
     */
    public function findAll(?string $estado = null)
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.mesa_id,
                        m.numero AS mesa_numero,
                        p.estado_id,
                        ep.nombre AS estado,
                        p.fecha_creacion
                    FROM pedidos p
                    LEFT JOIN mesas m ON p.mesa_id = m.id
                    LEFT JOIN estados_pedido ep ON p.estado_id = ep.id";

            $params = [];
            if ($estado) {
                // Normalizar lista de estados en minúscula
                $parts = array_filter(array_map(function ($x) { return strtolower(trim($x)); }, explode(',', $estado)));
                if (!empty($parts)) {
                    $placeholders = implode(',', array_fill(0, count($parts), '?'));
                    $sql .= " WHERE LOWER(ep.nombre) IN ($placeholders)";
                    $params = $parts;
                }
            }

            $sql .= " ORDER BY p.fecha_creacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $row) {
                $result[] = [
                    'id'          => (int)$row['id'],
                    'mesa_id'     => isset($row['mesa_id']) ? (int)$row['mesa_id'] : null,
                    'mesa_numero' => $row['mesa_numero'] ?? null,
                    'estado'      => $row['estado'] ?? null,
                    'estado_id'   => isset($row['estado_id']) ? (int)$row['estado_id'] : null,
                    'fecha'       => $row['fecha_creacion'] ?? null,
                ];
            }
            return $result;
        } catch (\PDOException $e) {
            // Fallback si falla el join/tabla estados_pedido: intentar por estado_id o sin filtro
            error_log('Error en PedidoModel::findAll (principal): ' . $e->getMessage());
            try {
                $sql = "SELECT 
                            p.id,
                            p.mesa_id,
                            m.numero AS mesa_numero,
                            p.estado_id,
                            p.fecha_creacion
                        FROM pedidos p
                        LEFT JOIN mesas m ON p.mesa_id = m.id";
                $params = [];
                if ($estado) {
                    $parts = array_filter(array_map(fn($x) => strtolower(trim($x)), explode(',', $estado)));
                    // Mapeo heurístico de nombres a IDs (ajustar según BD real)
                    $map = [
                        'pendiente' => 1,
                        'en preparacion' => 2,
                        'servido' => 3,
                        'pagado' => 4,
                        'cancelado' => 5,
                    ];
                    $ids = array_values(array_unique(array_filter(array_map(fn($s) => $map[$s] ?? null, $parts))));
                    if (!empty($ids)) {
                        $in = implode(',', array_fill(0, count($ids), '?'));
                        $sql .= " WHERE p.estado_id IN ($in)";
                        $params = $ids;
                    }
                }
                $sql .= " ORDER BY p.fecha_creacion DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $res = [];
                foreach ($rows as $row) {
                    $res[] = [
                        'id'          => (int)$row['id'],
                        'mesa_id'     => isset($row['mesa_id']) ? (int)$row['mesa_id'] : null,
                        'mesa_numero' => $row['mesa_numero'] ?? null,
                        'estado_id'   => isset($row['estado_id']) ? (int)$row['estado_id'] : null,
                        'fecha'       => $row['fecha_creacion'] ?? null,
                    ];
                }
                return $res;
            } catch (\PDOException $e2) {
                error_log('Fallback en PedidoModel::findAll también falló: ' . $e2->getMessage());
                return false;
            }
        }
    }

    /**
     * Crea un pedido y sus detalles de forma transaccional
     */
    public function createPedido(int $mesaId, int $usuarioId, array $items, ?string $notas = null): int|false
    {
        try {
            $this->db->beginTransaction();

            // Resolver estado 'pendiente'
            $estadoId = null;
            try {
                $stmtEstado = $this->db->prepare("SELECT id FROM estados_pedido WHERE LOWER(nombre) = 'pendiente' LIMIT 1");
                $stmtEstado->execute();
                $rowEstado = $stmtEstado->fetch(\PDO::FETCH_ASSOC);
                if ($rowEstado && isset($rowEstado['id'])) {
                    $estadoId = (int)$rowEstado['id'];
                }
            } catch (Exception $e) {
            } catch (Exception $e) {
                // Continuar con fallback si la tabla no existe o falla
                $estadoId = null;
            }
            if ($estadoId === null) {
                // Fallback razonable (suele ser 1)
                $estadoId = 1;
            }

            // Calcular total desde los ítems (resolviendo precio desde productos cuando no se envía)
            $total = 0.0;
            $preciosCache = [];
            foreach ($items as $item) {
                $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : (isset($item['qty']) ? (int)$item['qty'] : 0);
                $precio = null;
                if (isset($item['precio_unitario'])) {
                    $precio = (float)$item['precio_unitario'];
                } elseif (isset($item['precio'])) {
                    $precio = (float)$item['precio'];
                } else {
                    $productoIdTmp = isset($item['producto_id']) ? (int)$item['producto_id'] : (isset($item['productoId']) ? (int)$item['productoId'] : 0);
                    if ($productoIdTmp > 0) {
                        if (!isset($preciosCache[$productoIdTmp])) {
                            $stmtProd = $this->db->prepare('SELECT precio FROM productos WHERE id = :id');
                            $stmtProd->execute(['id' => $productoIdTmp]);
                            $rowProd = $stmtProd->fetch(\PDO::FETCH_ASSOC);
                            $preciosCache[$productoIdTmp] = $rowProd ? (float)$rowProd['precio'] : 0.0;
                        }
                        $precio = $preciosCache[$productoIdTmp];
                    } else {
                        $precio = 0.0;
                    }
                }
                $total += ($cantidad * (float)$precio);
            }

            // Insertar cabecera del pedido
            $stmtPedido = $this->db->prepare(
                'INSERT INTO pedidos (mesa_id, usuario_id, estado_id, total, notas, fecha_creacion) VALUES (:mesa_id, :usuario_id, :estado_id, :total, :notas, NOW())'
            );
            $stmtPedido->bindValue(':mesa_id', $mesaId, \PDO::PARAM_INT);
            $stmtPedido->bindValue(':usuario_id', $usuarioId, \PDO::PARAM_INT);
            $stmtPedido->bindValue(':estado_id', $estadoId, \PDO::PARAM_INT);
            $stmtPedido->bindValue(':total', $total);
            $stmtPedido->bindValue(':notas', $notas);
            if (!$stmtPedido->execute()) {
                $this->db->rollBack();
                return false;
            }
            $pedidoId = (int)$this->db->lastInsertId();

            // Insertar detalles del pedido
            $stmtDetalle = $this->db->prepare(
                'INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, notas, fecha_creacion) VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal, :notas, NOW())'
            );
            foreach ($items as $item) {
                $productoId = isset($item['producto_id']) ? (int)$item['producto_id'] : (isset($item['productoId']) ? (int)$item['productoId'] : 0);
                $cantidad   = isset($item['cantidad']) ? (int)$item['cantidad'] : (isset($item['qty']) ? (int)$item['qty'] : 0);
                // Resolver precio unitario si no viene en el payload
                if (isset($item['precio_unitario'])) {
                    $precio = (float)$item['precio_unitario'];
                } elseif (isset($item['precio'])) {
                    $precio = (float)$item['precio'];
                } else {
                    if (!isset($preciosCache[$productoId])) {
                        $stmtProd = $this->db->prepare('SELECT precio FROM productos WHERE id = :id');
                        $stmtProd->execute(['id' => $productoId]);
                        $rowProd = $stmtProd->fetch(\PDO::FETCH_ASSOC);
                        $preciosCache[$productoId] = $rowProd ? (float)$rowProd['precio'] : 0.0;
                    }
                    $precio = $preciosCache[$productoId];
                }
                $subtotal   = $cantidad * $precio;
                $notaItem   = $item['notas'] ?? null;

                if ($productoId <= 0 || $cantidad <= 0) {
                    continue; // Saltar ítems inválidos
                }

                $stmtDetalle->bindValue(':pedido_id', $pedidoId, \PDO::PARAM_INT);
                $stmtDetalle->bindValue(':producto_id', $productoId, \PDO::PARAM_INT);
                $stmtDetalle->bindValue(':cantidad', $cantidad, \PDO::PARAM_INT);
                $stmtDetalle->bindValue(':precio_unitario', $precio);
                $stmtDetalle->bindValue(':subtotal', $subtotal);
                $stmtDetalle->bindValue(':notas', $notaItem);
                if (!$stmtDetalle->execute()) {
                    $this->db->rollBack();
                    return false;
                }
            }

            $this->db->commit();
            return $pedidoId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en PedidoModel::createPedido: ' . $e->getMessage());
            return false;
        }
    }
    public function actualizarEstadoPedido(int $pedidoId, int $nuevoEstadoId): bool
    {
        return $this->actualizarEstadoPedidoPorId($pedidoId, $nuevoEstadoId);
    }

    public function actualizarEstadoPedidoPorId(int $pedidoId, int $nuevoEstadoId): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE pedidos SET estado_id = :estado_id WHERE id = :id');
            $stmt->execute(['estado_id' => $nuevoEstadoId, 'id' => $pedidoId]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log('Error en PedidoModel::actualizarEstadoPedidoPorId: ' . $e->getMessage());
            return false;
        }
    }

    public function eliminarPedido(int $pedidoId): bool
    {
        return $this->actualizarEstadoPedidoPorId($pedidoId, 0);
    }
    /**
     * Obtiene la cabecera del pedido y sus items
     */
    public function getPedidoWithDetails(int $pedidoId): ?array
    {
        // Cabecera
        $stmt = $this->db->prepare('SELECT * FROM pedidos WHERE id = :id');
        $stmt->execute(['id' => $pedidoId]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$pedido) return null;

        // Items desde detalles_pedido
        $stmtI = $this->db->prepare('SELECT producto_id, cantidad, precio_unitario, subtotal, notas FROM detalles_pedido WHERE pedido_id = :id');
        $stmtI->execute(['id' => $pedidoId]);
        $items = $stmtI->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'id'         => (int)$pedido['id'],
            'mesa_id'    => isset($pedido['mesa_id']) ? (int)$pedido['mesa_id'] : null,
            'usuario_id' => isset($pedido['usuario_id']) ? (int)$pedido['usuario_id'] : null,
            'estado_id'  => isset($pedido['estado_id']) ? (int)$pedido['estado_id'] : null,
            'total'      => (float)($pedido['total'] ?? 0),
            'notas'      => $pedido['notas'] ?? null,
            'fecha'      => $pedido['fecha_creacion'] ?? date('Y-m-d H:i:s'),
            'items'      => $items,
        ];
    }

    /**
     * Alias para compatibilidad con versiones previas
     */
    public function obtenerPedidoConItems(int $pedidoId): ?array
    {
        return $this->getPedidoWithDetails($pedidoId);
    }

    public function marcarFacturadoElectronico(int $pedidoId, ?string $cufe, ?string $numero)
    {
        try {
            $stmt = $this->db->prepare('UPDATE pedidos SET facturacion_electronica = 1, cufe = :cufe, numero_factura = :numero WHERE id = :id');
            $stmt->execute(['cufe' => $cufe, 'numero' => $numero, 'id' => $pedidoId]);
        } catch (\PDOException $e) {
            // Si faltan columnas, intentamos agregarlas y reintentar
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                try {
                    $this->db->exec("ALTER TABLE pedidos 
                        ADD COLUMN IF NOT EXISTS facturacion_electronica TINYINT(1) DEFAULT 0,
                        ADD COLUMN IF NOT EXISTS cufe VARCHAR(128) NULL,
                        ADD COLUMN IF NOT EXISTS numero_factura VARCHAR(64) NULL");
                } catch (\PDOException $e2) {
                    error_log('No se pudieron crear columnas de FE en pedidos: ' . $e2->getMessage());
                    return; // salir silenciosamente para no romper el flujo
                }
                try {
                    $stmt = $this->db->prepare('UPDATE pedidos SET facturacion_electronica = 1, cufe = :cufe, numero_factura = :numero WHERE id = :id');
                    $stmt->execute(['cufe' => $cufe, 'numero' => $numero, 'id' => $pedidoId]);
                } catch (\PDOException $e3) {
                    error_log('Falló el marcado FE incluso tras crear columnas: ' . $e3->getMessage());
                }
            } else {
                error_log('Error en marcarFacturadoElectronico: ' . $e->getMessage());
            }
        }
    }

    /**
     * Marca el pedido como facturado cambiando su estado a 'facturado'.
     */
    public function facturarPedido(int $pedidoId): bool
    {
        try {
            // Resolver estado 'facturado'
            $estadoId = null;
            try {
                $stmtEstado = $this->db->prepare("SELECT id FROM estados_pedido WHERE LOWER(nombre) = 'facturado' LIMIT 1");
                $stmtEstado->execute();
                $rowEstado = $stmtEstado->fetch(\PDO::FETCH_ASSOC);
                if ($rowEstado && isset($rowEstado['id'])) {
                    $estadoId = (int)$rowEstado['id'];
                }
            } catch (Exception $e) {
                $estadoId = null;
            }
            if ($estadoId === null) {
                // Fallback razonable si la tabla no existe o el estado no está creado
                $estadoId = 3; // comúnmente 'facturado' suele ser 3; ajustar según BD real
            }

            $stmt = $this->db->prepare('UPDATE pedidos SET estado_id = :estado_id, fecha_actualizacion = NOW() WHERE id = :id');
            return $stmt->execute(['estado_id' => $estadoId, 'id' => $pedidoId]);
        } catch (Exception $e) {
            error_log('Error en PedidoModel::facturarPedido: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda el pedido y sus detalles en las tablas de historial.
     */
    public function guardarEnHistorial(int $pedidoId): bool
    {
        try {
            // Obtener cabecera y detalles
            $pedido = $this->getPedidoWithDetails($pedidoId);
            if (!$pedido) return false;

            // Enriquecer con datos de mesa y usuario si están disponibles
            $mesaNumero = null;
            try {
                if (!empty($pedido['mesa_id'])) {
                    $stmtMesa = $this->db->prepare('SELECT numero FROM mesas WHERE id = :id');
                    $stmtMesa->execute(['id' => (int)$pedido['mesa_id']]);
                    $rowMesa = $stmtMesa->fetch(\PDO::FETCH_ASSOC);
                    $mesaNumero = $rowMesa['numero'] ?? null;
                }
            } catch (Exception $e) {}

            $usuarioNombre = null;
            try {
                if (!empty($pedido['usuario_id'])) {
                    $stmtUsu = $this->db->prepare('SELECT nombre FROM usuarios WHERE id = :id');
                    $stmtUsu->execute(['id' => (int)$pedido['usuario_id']]);
                    $rowUsu = $stmtUsu->fetch(\PDO::FETCH_ASSOC);
                    $usuarioNombre = $rowUsu['nombre'] ?? null;
                }
            } catch (Exception $e) {}

            // Obtener nombre del estado final
            $estadoFinal = null;
            try {
                if (!empty($pedido['estado_id'])) {
                    $stmtEstado = $this->db->prepare('SELECT nombre FROM estados_pedido WHERE id = :id');
                    $stmtEstado->execute(['id' => (int)$pedido['estado_id']]);
                    $rowEst = $stmtEstado->fetch(\PDO::FETCH_ASSOC);
                    $estadoFinal = $rowEst['nombre'] ?? null;
                }
            } catch (Exception $e) {}

            // Insertar historial_pedidos
            $stmtHist = $this->db->prepare(
                'INSERT INTO historial_pedidos (pedido_id, mesa_id, mesa_numero, usuario_id, usuario_nombre, estado_final, total, fecha_creacion, fecha_finalizacion) 
                 VALUES (:pedido_id, :mesa_id, :mesa_numero, :usuario_id, :usuario_nombre, :estado_final, :total, :fecha_creacion, NOW())'
            );
            $ok = $stmtHist->execute([
                'pedido_id'      => (int)$pedido['id'],
                'mesa_id'        => $pedido['mesa_id'] ?? null,
                'mesa_numero'    => $mesaNumero,
                'usuario_id'     => $pedido['usuario_id'] ?? null,
                'usuario_nombre' => $usuarioNombre,
                'estado_final'   => $estadoFinal,
                'total'          => $pedido['total'] ?? 0,
                'fecha_creacion' => $pedido['fecha'] ?? date('Y-m-d H:i:s'),
            ]);
            if (!$ok) return false;

            $historialId = (int)$this->db->lastInsertId();

            // Insertar historial_detalles_pedido
            if (!empty($pedido['items'])) {
                $stmtDet = $this->db->prepare(
                    'INSERT INTO historial_detalles_pedido (historial_pedido_id, producto_id, producto_nombre, cantidad, precio_unitario, subtotal, rentabilidad) 
                     VALUES (:historial_pedido_id, :producto_id, :producto_nombre, :cantidad, :precio_unitario, :subtotal, :rentabilidad)'
                );
                foreach ($pedido['items'] as $item) {
                    $productoId = (int)($item['producto_id'] ?? 0);
                    $cantidad   = (int)($item['cantidad'] ?? 0);
                    $precio     = (float)($item['precio_unitario'] ?? 0);
                    $subtotal   = (float)($item['subtotal'] ?? ($cantidad * $precio));
                    // Resolver nombre de producto si no viene
                    $productoNombre = $item['producto_nombre'] ?? $item['nombre_producto'] ?? null;
                    if (!$productoNombre && $productoId > 0) {
                        try {
                            $stmtPN = $this->db->prepare('SELECT nombre FROM productos WHERE id = :id');
                            $stmtPN->execute(['id' => $productoId]);
                            $rowPN = $stmtPN->fetch(\PDO::FETCH_ASSOC);
                            $productoNombre = $rowPN['nombre'] ?? null;
                        } catch (Exception $e) {}
                    }
                    // Rentabilidad (stub simple): subtotal * 0.3, ajustar a su lógica real
                    $rentabilidad = round($subtotal * 0.3, 2);

                    if ($productoId <= 0 || $cantidad <= 0) {
                        continue;
                    }

                    $stmtDet->execute([
                        'historial_pedido_id' => $historialId,
                        'producto_id'         => $productoId,
                        'producto_nombre'     => $productoNombre,
                        'cantidad'            => $cantidad,
                        'precio_unitario'     => $precio,
                        'subtotal'            => $subtotal,
                        'rentabilidad'        => $rentabilidad,
                    ]);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log('Error en PedidoModel::guardarEnHistorial: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerEstadoFacturacionElectronica(int $pedidoId): array
    {
        $stmt = $this->db->prepare('SELECT facturacion_electronica, cufe, numero_factura FROM pedidos WHERE id = :id');
        $stmt->execute(['id' => $pedidoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) throw new Exception('Pedido no encontrado', 404);
        return [
            'emitida' => (bool)$row['facturacion_electronica'],
            'cufe'    => $row['cufe'] ?? null,
            'numero'  => $row['numero_factura'] ?? null,
        ];
    }
}
