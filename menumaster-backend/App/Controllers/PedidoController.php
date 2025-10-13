<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Models\MesaModel;
use App\Models\PagosModel;
use App\Models\MetodoPagoModel;
use App\EstadosMesa;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Utils\Validator;
use PDO;
use Exception;

class PedidoController
{
    private $pedidoModel;
    private $mesaModel;
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->pedidoModel = new PedidoModel($this->db);
        $this->mesaModel = new MesaModel($this->db);
    }

    /**
     * GET /api/pedidos
     * GET /api/pedidos?estado=pendiente,en preparacion
     */
    public function index()
    {
        // Obtener parámetro 'estado' desde query string (forma segura)
        $estado = isset($_GET['estado']) ? trim($_GET['estado']) : null;

        // Mapear sinónimos de estados enviados desde frontend a nombres internos
        if ($estado) {
            $synonyms = [
                'listo para servir' => 'servido',
            ];
            $parts = array_map('trim', explode(',', strtolower($estado)));
            $mapped = array_map(function ($p) use ($synonyms) {
                return $synonyms[$p] ?? $p;
            }, $parts);
            $estado = implode(',', $mapped);
        }

        try {
            if ($estado) {
                // Buscar pedidos filtrados por estado
                $pedidos = $this->pedidoModel->findAll($estado);
            } else {
                // Obtener todos los pedidos
                $pedidos = $this->pedidoModel->findAll();
            }

            if ($pedidos === false) {
                throw new Exception("Error al obtener los pedidos.");
            }

            // Enviar respuesta exitosa
            $this->sendResponse(200, "Pedidos obtenidos correctamente", null, $pedidos);

        } catch (Exception $e) {
            error_log("Error en PedidoController::index: " . $e->getMessage());
            // Enviar error con código 500
            $this->sendResponse(500, null, $e->getMessage());
        }
    }

    /**
     * GET /api/pedidos/{id}
     */
    public function show(int $id): void
    {
        try {
            $detalles = $this->pedidoModel->getPedidoWithDetails($id);
            if (!$detalles) {
                $this->sendResponse(404, null, "Pedido no encontrado");
                return;
            }
            $this->sendResponse(200, "Detalles del pedido obtenidos correctamente", null, $detalles);
        } catch (Exception $e) {
            error_log("Error en PedidoController::show: " . $e->getMessage());
            $this->sendResponse(500, null, "Error interno del servidor");
        }
    }

    /**
     * POST /api/pedidos
     */
    public function store(array $data): void
    {
        try {
            Validator::validate($data, [
                'mesa_id' => 'required',
                'items' => 'required'
            ]);

            $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
            if (!$token) {
                $this->sendResponse(401, null, "Token no encontrado");
                return;
            }
            
            $payload = AuthController::decodeTokenData($token);
            $usuarioId = $payload['data']->id ?? null;
            if (!$usuarioId) {
                $this->sendResponse(401, null, "Token inválido");
                return;
            }
            
            $notas = $data['notas'] ?? null;
            
            // Verificar stock de productos en el menú del día antes de crear el pedido
            $this->verificarStockProductos($data['items']);
            
            $pedidoId = $this->pedidoModel->createPedido($data['mesa_id'], $usuarioId, $data['items'], $notas);
            if (!$pedidoId) {
                $this->sendResponse(500, null, "Error al crear el pedido. Verifique el stock o los datos del producto");
                return;
            }
            
            // Cambiar el estado de la mesa a ocupada (por ID) después de crear el pedido
            if (!$this->mesaModel->cambiarEstadoPorId($data['mesa_id'], EstadosMesa::OCUPADA)) {
                error_log("Advertencia: No se pudo cambiar el estado de la mesa {$data['mesa_id']} a ocupada");
            }
            
            $nuevoPedido = $this->pedidoModel->getPedidoWithDetails($pedidoId);
            
            // Imprimir automáticamente en cocina después de crear el pedido
            $this->imprimirPedidoCocina($nuevoPedido);
            
            $this->sendResponse(201, "Pedido creado exitosamente", null, $nuevoPedido);
            
        } catch (Exception $e) {
            error_log("Error en PedidoController::store: " . $e->getMessage());
            $this->sendResponse(500, null, $e->getMessage());
        }
    }
    
    /**
     * Verifica el stock de productos antes de crear un pedido
     */
    private function verificarStockProductos(array $items): void
    {
        // Para esta implementación simplificada, omitimos la verificación de stock
        // ya que la tabla menu_del_dia no tiene las columnas stock_actual y stock_limite
        // En un sistema completo, esto se implementaría con la estructura correcta
        return;
    }
    
    /**
     * PUT /api/pedidos/{id}/estado
     */
    public function updateStatus(int $id, array $data): void
    {
        try {
            // Permitir estado_id directamente; mantener compatibilidad con 'estado' por nombre
            
            // Obtener información del pedido antes de actualizar el estado
            $pedido = $this->pedidoModel->getPedidoWithDetails($id);
            if (!$pedido) {
                $this->sendResponse(404, null, "Pedido no encontrado");
                return;
            }
            
            if (isset($data['estado_id'])) {
                if (!$this->pedidoModel->actualizarEstadoPedidoPorId($id, (int)$data['estado_id'])) {
                    $this->sendResponse(500, null, "Error al actualizar el estado del pedido");
                    return;
                }
            } else {
                Validator::validate($data, ['estado' => 'required']);
                // Normalizar nombre de estado para coincidir con la BD (espacios, guiones, mayúsculas)
                $estadoNormalizado = $this->normalizarEstadoPedidoNombre((string)$data['estado']);
                if ($estadoNormalizado === null) {
                    $this->sendResponse(422, null, "Estado de pedido inválido");
                    return;
                }
                if (!$this->pedidoModel->actualizarEstadoPedido($id, $estadoNormalizado)) {
                    $this->sendResponse(500, null, "Error al actualizar el estado del pedido");
                    return;
                }
            }
            
            // Nota: Se desactiva la auto-liberación de mesa al cambiar estado del pedido.
            // La liberación ahora se realiza explícitamente via endpoint de mesas.
            
            $this->sendResponse(200, "Estado del pedido actualizado correctamente");
            
        } catch (Exception $e) {
            error_log("Error en PedidoController::updateStatus: " . $e->getMessage());
            $this->sendResponse(500, null, $e->getMessage());
        }
    }

    /**
     * Normaliza nombres de estado enviados por el frontend para que coincidan con la BD.
     * Retorna el nombre exacto en la tabla `estados_pedido` o null si no se reconoce.
     */
    private function normalizarEstadoPedidoNombre(string $estado): ?string
    {
        $e = strtolower(trim($estado));
        // Reemplazar guiones/underscores por espacio y colapsar dobles espacios
        $e = preg_replace('/[-_]+/',' ', $e);
        $e = preg_replace('/\s{2,}/',' ', $e);
        // Mapeo de sinónimos conocidos
        $map = [
            'pendiente' => 'pendiente',
            'en preparacion' => 'en preparacion',
            'en preparación' => 'en preparacion',
            'enpreparacion' => 'en preparacion',
            'listo para servir' => 'servido',
            'servido' => 'servido',
            'entregado' => 'servido',
            'completado' => 'servido',
            'finalizado' => 'servido',
            'pagado' => 'pagado',
            'facturado' => 'pagado',
            'cancelado' => 'cancelado'
        ];
        if (isset($map[$e])) {
            return $map[$e];
        }
        // Si no coincide exactamente, devolver el valor normalizado como intento final
        // para nombres ya correctos que vengan con variaciones.
        $permitidos = ['pendiente','en preparacion','servido','pagado','cancelado'];
        return in_array($e, $permitidos) ? $e : null;
    }
    
    /**
     * POST /api/pedidos/{id}/facturar
     */
    public function facturar(int $id, array $data): void
    {
        try {
            // Obtener la información del pedido antes de facturarlo para saber qué mesa liberar
            $pedido = $this->pedidoModel->getPedidoWithDetails($id);
            if (!$pedido) {
                $this->sendResponse(404, null, "Pedido no encontrado");
                return;
            }
            // Validación de datos de pago
            $metodoId = isset($data['metodo_id']) ? (int)$data['metodo_id'] : null;
            $metodoPagoTexto = isset($data['metodo_pago']) ? trim($data['metodo_pago']) : null;
            $dividir = !empty($data['dividir']);
            $personas = isset($data['personas']) && (int)$data['personas'] > 0 ? (int)$data['personas'] : 1;

            if (!$metodoId && !$metodoPagoTexto) {
                $this->sendResponse(422, null, "Se requiere 'metodo_id' o 'metodo_pago'.");
                return;
            }

            // Calcular total del pedido (a partir de los detalles)
            $totalPedido = 0.0;
            if (!empty($pedido['items'])) {
                foreach ($pedido['items'] as $item) {
                    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;
                    $precio = isset($item['precio_unitario']) ? (float)$item['precio_unitario'] : 0.0;
                    $totalPedido += ($cantidad * $precio);
                }
            }

            // Monto a registrar (por persona si se divide)
            $monto = $dividir && $personas > 1 ? ($totalPedido / $personas) : $totalPedido;

            // Resolver método de pago (id y nombre)
            $metodoPagoNombre = $metodoPagoTexto;
            // Mapeo de sinónimos de método de pago
            $metodoPagoTextoNorm = $metodoPagoTexto ? mb_strtolower($metodoPagoTexto) : null;
            $mapMetodos = [
                'efectivo' => 1,
                'cash' => 1,
                'tarjeta' => 2, // por defecto crédito
                'tarjeta de crédito' => 2,
                'credito' => 2,
                'crédito' => 2,
                'tarjeta de débito' => 3,
                'debito' => 3,
                'débito' => 3,
                'transferencia' => 4,
                'transfer' => 4,
            ];

            if (!$metodoId && $metodoPagoTextoNorm && isset($mapMetodos[$metodoPagoTextoNorm])) {
                $metodoId = $mapMetodos[$metodoPagoTextoNorm];
            }

            if ($metodoId) {
                $stmtMetodo = $this->db->prepare('SELECT id, nombre FROM metodos_pago WHERE id = :id');
                $stmtMetodo->bindParam(':id', $metodoId, PDO::PARAM_INT);
                $stmtMetodo->execute();
                $metodo = $stmtMetodo->fetch(PDO::FETCH_ASSOC);
                if (!$metodo) {
                    $this->sendResponse(404, null, 'Método de pago no encontrado.');
                    return;
                }
                $metodoPagoNombre = $metodo['nombre'];
                $metodoId = (int)$metodo['id'];
            } else if ($metodoPagoTexto) {
                // Buscar por nombre si no se envió id (case-insensitive)
                $stmtMetodo = $this->db->prepare('SELECT id, nombre FROM metodos_pago WHERE LOWER(nombre) = LOWER(:nombre)');
                $stmtMetodo->bindParam(':nombre', $metodoPagoTexto);
                $stmtMetodo->execute();
                $metodo = $stmtMetodo->fetch(PDO::FETCH_ASSOC);
                if ($metodo) {
                    $metodoPagoNombre = $metodo['nombre'];
                    $metodoId = (int)$metodo['id'];
                } else {
                    $this->sendResponse(422, null, 'Método de pago inválido o no soportado.');
                    return;
                }
            }

            // Obtener usuario del token
            $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
            $usuarioId = 0;
            if ($token) {
                $payload = AuthController::decodeTokenData($token);
                if (isset($payload['data'])) {
                    if (is_array($payload['data'])) {
                        $usuarioId = $payload['data']['id'] ?? 0;
                    } elseif (is_object($payload['data'])) {
                        $usuarioId = $payload['data']->id ?? 0;
                    }
                }
            }
            // Fallback: usar usuario asociado al pedido si token no provee id válido
            if (!$usuarioId) {
                $usuarioId = isset($pedido['usuario_id']) ? (int)$pedido['usuario_id'] : 0;
            }

            // Registrar pago
            $pagosModel = new PagosModel($this->db);
            $pagosModel->pedido_id = $id;
            $pagosModel->monto = $monto;
            $pagosModel->metodo_pago_id = $metodoId;
            $pagosModel->usuario_id = $usuarioId;

            if (!$pagosModel->crear()) {
                $this->sendResponse(500, null, 'No se pudo registrar el pago.');
                return;
            }

            // Facturar pedido tras registrar el pago
            if (!$this->pedidoModel->facturarPedido($id)) {
                $this->sendResponse(500, null, "No se pudo facturar el pedido. Verifique el ID");
                return;
            }

            // Cambiar el estado de la mesa a disponible (por ID) después de facturar el pedido
            $mesaId = $pedido['mesa_id'] ?? null;
            if ($mesaId && !$this->mesaModel->cambiarEstadoPorId($mesaId, EstadosMesa::DISPONIBLE)) {
                error_log("Advertencia: No se pudo cambiar el estado de la mesa {$mesaId} a disponible");
            }

            // Preparar datos de pago para impresión del recibo
            $pedido['pago'] = [
                'metodo' => $metodoPagoNombre ?? 'Desconocido',
                'referencia' => 'PED-' . $id . '-' . date('YmdHis'),
            ];

            // Guardar en historial tras facturar
            try {
                $this->pedidoModel->guardarEnHistorial($id);
            } catch (Exception $e) {
                error_log('Advertencia: No se pudo guardar el pedido en historial: ' . $e->getMessage());
            }

            // Imprimir recibo
            $this->imprimirRecibo($pedido);

            $this->sendResponse(200, "Pedido facturado con éxito");
            
        } catch (Exception $e) {
            error_log("Error en PedidoController::facturar: " . $e->getMessage());
            $this->sendResponse(500, null, $e->getMessage());
        }
    }
    
    /**
     * Imprime un recibo para el pedido
     */
    private function imprimirRecibo(array $pedido): void
    {
        try {
            // Cargar el gestor de impresión
            $printerManager = new \App\Utils\PrinterManager();
            
            // Imprimir recibo
            $printerManager->printReceipt($pedido);
        } catch (Exception $e) {
            error_log("Error al imprimir recibo: " . $e->getMessage());
            // No lanzamos excepción para no interrumpir el flujo principal
        }
    }
    
    /**
     * Imprime un pedido automáticamente en cocina (método interno)
     */
    private function imprimirPedidoCocina(array $pedido): void
    {
        try {
            // Cargar el gestor de impresión
            $printerManager = new \App\Utils\PrinterManager();
            
            // Imprimir pedido en cocina sin contaminar la salida JSON
            // Se suprimen temporalmente los warnings/notices y se usa un buffer de salida
            $prevDisplayErrors = ini_get('display_errors');
            $prevErrorReporting = error_reporting();
            ini_set('display_errors', '0');
            error_reporting(0);
            ob_start();
            $resultado = @$printerManager->printOrder($pedido);
            // Limpiar cualquier salida generada durante la impresión
            ob_end_clean();
            // Restaurar configuración previa
            error_reporting($prevErrorReporting);
            ini_set('display_errors', $prevDisplayErrors);
            
            if ($resultado) {
                error_log("Pedido ID {$pedido['id']} enviado automáticamente a impresión en cocina");
            } else {
                error_log("Advertencia: No se pudo imprimir automáticamente el pedido ID {$pedido['id']} en cocina");
            }
        } catch (Exception $e) {
            error_log("Error al imprimir automáticamente en cocina el pedido ID {$pedido['id']}: " . $e->getMessage());
            // No lanzamos excepción para no interrumpir el flujo principal de creación del pedido
        }
    }

    /**
     * POST /api/pedidos/{id}/imprimir
     * Imprime un pedido para la cocina
     */
    public function imprimirPedido(int $id): void
    {
        try {
            // Obtener detalles del pedido
            $pedido = $this->pedidoModel->getPedidoWithDetails($id);
            if (!$pedido) {
                $this->sendResponse(404, null, "Pedido no encontrado");
                return;
            }
            
            // Cargar el gestor de impresión
            $printerManager = new \App\Utils\PrinterManager();
            
            // Imprimir pedido
            $resultado = $printerManager->printOrder($pedido);
            
            if ($resultado) {
                $this->sendResponse(200, "Pedido enviado a impresión correctamente");
            } else {
                $this->sendResponse(500, null, "No se pudo imprimir el pedido. Verifique la configuración de la impresora");
            }
        } catch (Exception $e) {
            error_log("Error en PedidoController::imprimirPedido: " . $e->getMessage());
            $this->sendResponse(500, null, $e->getMessage());
        }
    }



    /**
     * DELETE /api/pedidos/{id}
     */
    public function destroy(int $id): void
    {
        try {
            // MEJORA: Se añade una verificación para asegurar que el pedido existe antes de intentar borrarlo.
            if (!$this->pedidoModel->getPedidoWithDetails($id)) {
                $this->sendResponse(404, null, "Pedido no encontrado");
                return;
            }

            if (!$this->pedidoModel->eliminarPedido($id)) {
                $this->sendResponse(500, null, "Error al eliminar el pedido");
                return;
            }
            
            $this->sendResponse(204);
            
        } catch (Exception $e) {
            error_log("Error en PedidoController::destroy: " . $e->getMessage());
            $this->sendResponse(500, null, $e->getMessage());
        }
    }
    
    // --- Helper para enviar respuestas ---
    private function sendResponse(int $statusCode, $message = null, $error = null, $data = null): void
    {
        $isCli = (php_sapi_name() === 'cli');

        if (!$isCli) {
            http_response_code($statusCode);
        }

        if ($statusCode === 204) {
            if ($isCli) {
                return;
            }
            exit;
        }

        $response = [
            'success' => $statusCode < 400,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($error) {
            $response['error'] = $error;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Evitar warnings de headers ya enviados; en CLI no enviar headers
        if (!$isCli && !headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo $json;

        if ($isCli) {
            return;
        }
        exit;
    }
}