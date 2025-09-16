<?php
namespace App\Controllers;

use App\Models\Pedido;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use PDO;
use Exception;

class PedidoController
{
    private $pedidoModel;
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->pedidoModel = new Pedido($this->db);
    }

    /**
     * GET /api/pedidos
     * GET /api/pedidos?estado=pendiente,en preparacion
     */
    public function index(): void
    {
        $estados = $_GET['estado'] ?? null;
        $pedidos = $this->pedidoModel->findAll($estados);
        if ($pedidos === false) {
            throw new Exception("Error al obtener los pedidos.", 500);
        }
        $this->sendResponse(200, $pedidos);
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
        if (empty($data['mesa_id']) || empty($data['items'])) {
            throw new Exception("El ID de la mesa y los ítems son requeridos.", 400);
        }

        // Obtenemos el ID del usuario que está creando el pedido
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token no encontrado.", 401);
        $payload = AuthController::decodeTokenData($token);
        $usuarioId = $payload['id'] ?? null;
        if (!$usuarioId) throw new Exception("Token inválido.", 401);
        
        $notas = $data['notas'] ?? null;
        
        $pedidoId = $this->pedidoModel->createPedido($data['mesa_id'], $usuarioId, $data['items'], $notas);
        if (!$pedidoId) {
            throw new Exception("Error al crear el pedido. Verifique el stock de los productos.", 500);
        }
        
        $nuevoPedido = $this->pedidoModel->getPedidoWithDetails($pedidoId);
        $this->sendResponse(201, $nuevoPedido);
    }
    
    /**
     * PUT /api/pedidos/{id}/estado
     */
    public function updateStatus(int $id, array $data): void
    {
        if (empty($data['estado'])) {
            throw new Exception("El nuevo estado es requerido.", 400);
        }
        
        if (!$this->pedidoModel->actualizarEstadoPedido($id, $data['estado'])) {
            throw new Exception("Error al actualizar el estado del pedido.", 500);
        }
        
        $this->sendResponse(200, ["mensaje" => "Estado del pedido actualizado."]);
    }
    
    /**
     * POST /api/pedidos/{id}/facturar
     */
    public function facturar(int $id, array $data): void
    {
        // La lógica de facturación real (cálculo de totales, impuestos, etc.)
        // iría aquí o en un 'FacturaController'. Por ahora, solo cambiamos el estado.
        if (!$this->pedidoModel->facturarPedido($id)) {
            throw new Exception("No se pudo facturar el pedido.", 500);
        }
        
        $this->sendResponse(200, ["mensaje" => "Pedido facturado con éxito."]);
    }

    /**
     * DELETE /api/pedidos/{id}
     */
    public function destroy(int $id): void
    {
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