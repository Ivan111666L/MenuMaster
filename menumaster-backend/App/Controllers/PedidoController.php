<?php
namespace app\Controllers;

use app\Models\PedidoModel;
use app\Models\MesaModel;
use app\Middleware\AuthMiddleware;
use app\Controllers\AuthController;
use app\Utils\Validator;
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
        
        $this->sendResponse(200, ["mensaje" => "Pedido facturado con éxito."]);
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