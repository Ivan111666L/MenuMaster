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
        $this->sendResponse(200, $pedidos);

    } catch (Exception $e) {
        // Enviar error con código 500
        $this->sendResponse(500, ['error' => $e->getMessage()]);
    }
}

    /**
     * GET /api/pedidos/{id}
     */
    public function show(int $id): void
    {
        $detalles = $this->pedidoModel->getPedidoWithDetails($id);
        if (!$detalles) {
            throw new Exception("Pedido no encontrado.", 404);
        }
        $this->sendResponse(200, $detalles);
    }

    /**
     * POST /api/pedidos
     */
    public function store(array $data): void
    {
        Validator::validate($data, [
            'mesa_id' => 'required',
            'items' => 'required'
        ]);

        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token no encontrado.", 401);
        
        $payload = AuthController::decodeTokenData($token);
        $usuarioId = $payload['id'] ?? null;
        if (!$usuarioId) throw new Exception("Token inválido.", 401);
        
        $notas = $data['notas'] ?? null;
        
        // Verificar stock de productos en el menú del día antes de crear el pedido
        $this->verificarStockProductos($data['items']);
        
        $pedidoId = $this->pedidoModel->createPedido($data['mesa_id'], $usuarioId, $data['items'], $notas);
        if (!$pedidoId) {
            throw new Exception("Error al crear el pedido. Verifique el stock o los datos del producto.", 500);
        }
        
        // Cambiar el estado de la mesa a 'ocupada' después de crear el pedido
        if (!$this->mesaModel->cambiarEstado($data['mesa_id'], 'ocupada')) {
            error_log("Advertencia: No se pudo cambiar el estado de la mesa {$data['mesa_id']} a ocupada");
        }
        
        $nuevoPedido = $this->pedidoModel->getPedidoWithDetails($pedidoId);
        $this->sendResponse(201, $nuevoPedido);
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
        Validator::validate($data, ['estado' => 'required']);
        
        // Obtener información del pedido antes de actualizar el estado
        $pedido = $this->pedidoModel->getPedidoWithDetails($id);
        if (!$pedido) {
            throw new Exception("Pedido no encontrado.", 404);
        }
        
        if (!$this->pedidoModel->actualizarEstadoPedido($id, $data['estado'])) {
            throw new Exception("Error al actualizar el estado del pedido.", 500);
        }
        
        // Si el pedido se marca como entregado o completado, liberar la mesa
        $estadosQueLiberanMesa = ['entregado', 'completado', 'finalizado'];
        if (in_array(strtolower($data['estado']), $estadosQueLiberanMesa)) {
            $mesaId = $pedido['mesa_id'] ?? null;
            if ($mesaId && !$this->mesaModel->cambiarEstado($mesaId, 'disponible')) {
                error_log("Advertencia: No se pudo cambiar el estado de la mesa {$mesaId} a disponible");
            }
        }
        
        $this->sendResponse(200, ["mensaje" => "Estado del pedido actualizado."]);
    }
    
    /**
     * POST /api/pedidos/{id}/facturar
     */
    public function facturar(int $id, array $data): void
    {
        // Obtener la información del pedido antes de facturarlo para saber qué mesa liberar
        $pedido = $this->pedidoModel->getPedidoWithDetails($id);
        if (!$pedido) {
            throw new Exception("Pedido no encontrado.", 404);
        }
        
        // En una implementación real, aquí se validaría $data['metodo_pago'], etc.
        if (!$this->pedidoModel->facturarPedido($id)) {
            throw new Exception("No se pudo facturar el pedido. Verifique el ID.", 500);
        }
        
        // Cambiar el estado de la mesa a 'disponible' después de facturar el pedido
        $mesaId = $pedido['mesa_id'] ?? null;
        if ($mesaId && !$this->mesaModel->cambiarEstado($mesaId, 'disponible')) {
            error_log("Advertencia: No se pudo cambiar el estado de la mesa {$mesaId} a disponible");
        }
        
        // Imprimir recibo
        $this->imprimirRecibo($pedido);
        
        $this->sendResponse(200, ["mensaje" => "Pedido facturado con éxito."]);
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
     * POST /api/pedidos/{id}/imprimir
     * Imprime un pedido para la cocina
     */
    public function imprimirPedido(int $id): void
    {
        try {
            // Obtener detalles del pedido
            $pedido = $this->pedidoModel->getPedidoWithDetails($id);
            if (!$pedido) {
                throw new Exception("Pedido no encontrado.", 404);
            }
            
            // Cargar el gestor de impresión
            $printerManager = new \App\Utils\PrinterManager();
            
            // Imprimir pedido
            $resultado = $printerManager->printOrder($pedido);
            
            if ($resultado) {
                $this->sendResponse(200, ["mensaje" => "Pedido enviado a impresión correctamente."]);
            } else {
                throw new Exception("No se pudo imprimir el pedido. Verifique la configuración de la impresora.", 500);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ["error" => $e->getMessage()]);
        }
    }



    /**
     * DELETE /api/pedidos/{id}
     */
    public function destroy(int $id): void
    {
        // MEJORA: Se añade una verificación para asegurar que el pedido existe antes de intentar borrarlo.
        if (!$this->pedidoModel->getPedidoWithDetails($id)) {
            throw new Exception("Pedido no encontrado.", 404);
        }

        if (!$this->pedidoModel->eliminarPedido($id)) {
            throw new Exception("Error al eliminar el pedido.", 500);
        }
        $this->sendResponse(204, null);
    }
    
    // --- Helper para enviar respuestas ---
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }
}