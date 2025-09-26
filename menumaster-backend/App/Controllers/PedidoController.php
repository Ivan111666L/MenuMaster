<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Models\MesaModel;
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
            $usuarioId = $payload['data']['id'] ?? null;
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
            
            // Cambiar el estado de la mesa a 'ocupada' después de crear el pedido
            if (!$this->mesaModel->cambiarEstado($data['mesa_id'], 'ocupada')) {
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
     * Verifica el stock disponible de los productos en el menú del día
     */
    private function verificarStockProductos(array $items): void
    {
        $productosAVerificar = [];
        
        // Recopilar todos los productos individuales y de combos
        foreach ($items as $item) {
            // Si es un producto individual
            if (!isset($item['es_combo']) || !$item['es_combo']) {
                if (isset($item['producto_id'])) {
                    $productoId = $item['producto_id'];
                    $cantidad = $item['cantidad'] ?? 1;
                    
                    if (!isset($productosAVerificar[$productoId])) {
                        $productosAVerificar[$productoId] = 0;
                    }
                    $productosAVerificar[$productoId] += $cantidad;
                }
            }
            // Si es un combo, verificar cada elemento
            else if (isset($item['elementos']) && is_array($item['elementos'])) {
                foreach ($item['elementos'] as $elemento) {
                    if (isset($elemento['producto_id'])) {
                        $productoId = $elemento['producto_id'];
                        $cantidad = $elemento['cantidad'] ?? 1;
                        
                        if (!isset($productosAVerificar[$productoId])) {
                            $productosAVerificar[$productoId] = 0;
                        }
                        $productosAVerificar[$productoId] += $cantidad;
                    }
                }
            }
        }
        
        // Verificar stock para cada producto
        if (!empty($productosAVerificar)) {
            $placeholders = implode(',', array_fill(0, count($productosAVerificar), '?'));
            $query = "SELECT m.producto_id, m.stock_actual, m.stock_limite, p.nombre 
                     FROM menu_del_dia m 
                     JOIN productos p ON m.producto_id = p.id
                     WHERE m.producto_id IN ($placeholders) 
                     AND m.stock_limite IS NOT NULL";
            
            $stmt = $this->db->prepare($query);
            $i = 1;
            foreach (array_keys($productosAVerificar) as $productoId) {
                $stmt->bindValue($i++, $productoId, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            $productosConStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Verificar si hay suficiente stock para cada producto
            foreach ($productosConStock as $producto) {
                $productoId = $producto['producto_id'];
                $cantidadSolicitada = $productosAVerificar[$productoId] ?? 0;
                
                if ($cantidadSolicitada > $producto['stock_actual']) {
                    throw new Exception("Stock insuficiente para el producto '{$producto['nombre']}'. Disponible: {$producto['stock_actual']}, Solicitado: {$cantidadSolicitada}", 400);
                }
            }
        }
    }
    
    /**
     * PUT /api/pedidos/{id}/estado
     */
    public function updateStatus(int $id, array $data): void
    {
        try {
            Validator::validate($data, ['estado' => 'required']);
            
            // Obtener información del pedido antes de actualizar el estado
            $pedido = $this->pedidoModel->getPedidoWithDetails($id);
            if (!$pedido) {
                $this->sendResponse(404, null, "Pedido no encontrado");
                return;
            }
            
            if (!$this->pedidoModel->actualizarEstadoPedido($id, $data['estado'])) {
                $this->sendResponse(500, null, "Error al actualizar el estado del pedido");
                return;
            }
            
            // Si el pedido se marca como entregado o completado, liberar la mesa
            $estadosQueLiberanMesa = ['entregado', 'completado', 'finalizado'];
            if (in_array(strtolower($data['estado']), $estadosQueLiberanMesa)) {
                $mesaId = $pedido['mesa_id'] ?? null;
                if ($mesaId && !$this->mesaModel->cambiarEstado($mesaId, 'disponible')) {
                    error_log("Advertencia: No se pudo cambiar el estado de la mesa {$mesaId} a disponible");
                }
            }
            
            $this->sendResponse(200, "Estado del pedido actualizado correctamente");
            
        } catch (Exception $e) {
            error_log("Error en PedidoController::updateStatus: " . $e->getMessage());
            $this->sendResponse(500, null, $e->getMessage());
        }
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
            
            // En una implementación real, aquí se validaría $data['metodo_pago'], etc.
            if (!$this->pedidoModel->facturarPedido($id)) {
                $this->sendResponse(500, null, "No se pudo facturar el pedido. Verifique el ID");
                return;
            }
            
            // Cambiar el estado de la mesa a 'disponible' después de facturar el pedido
            $mesaId = $pedido['mesa_id'] ?? null;
            if ($mesaId && !$this->mesaModel->cambiarEstado($mesaId, 'disponible')) {
                error_log("Advertencia: No se pudo cambiar el estado de la mesa {$mesaId} a disponible");
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
            
            // Imprimir pedido en cocina
            $resultado = $printerManager->printOrder($pedido);
            
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
        http_response_code($statusCode);
        
        if ($statusCode === 204) {
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
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}