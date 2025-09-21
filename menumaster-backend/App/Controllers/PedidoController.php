<?php
namespace App\Controllers;

<<<<<<< HEAD
use app\Models\PedidoModel;
use app\Models\MesaModel;
use app\Middleware\AuthMiddleware;
use app\Controllers\AuthController;
use app\Utils\Validator;
=======
use App\Models\PedidoModel;
use App\Models\MesaModel;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Utils\Validator;
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
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
<<<<<<< HEAD
=======
    }

    public function exportarPDF($pedidoId)
    {
        try {
            // Obtener los datos del pedido
            $pedido = $this->pedidoModel->getPedidoById($pedidoId);
            if (!$pedido) {
                throw new Exception("Pedido no encontrado");
            }

            // Crear el PDF usando FPDF
            require_once __DIR__ . '/../../vendor/setasign/fpdf/fpdf.php';
            $pdf = new \FPDF();
            $pdf->AddPage();
            
            // Encabezado
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Pedido #' . $pedidoId, 0, 1, 'C');
            $pdf->Ln(10);
            
            // Detalles del pedido
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'Fecha: ' . $pedido['fecha_pedido'], 0, 1);
            $pdf->Cell(0, 10, 'Mesa: ' . $pedido['mesa_id'], 0, 1);
            $pdf->Cell(0, 10, 'Estado: ' . $pedido['estado'], 0, 1);
            $pdf->Ln(10);
            
            // Tabla de productos
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(90, 10, 'Producto', 1);
            $pdf->Cell(30, 10, 'Cantidad', 1);
            $pdf->Cell(30, 10, 'Precio', 1);
            $pdf->Cell(40, 10, 'Subtotal', 1);
            $pdf->Ln();
            
            // Obtener y listar los detalles del pedido
            $detalles = $this->pedidoModel->getDetallesPedido($pedidoId);
            $total = 0;
            
            $pdf->SetFont('Arial', '', 12);
            foreach ($detalles as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                $total += $subtotal;
                
                $pdf->Cell(90, 10, $detalle['nombre_producto'], 1);
                $pdf->Cell(30, 10, $detalle['cantidad'], 1);
                $pdf->Cell(30, 10, '$' . number_format($detalle['precio_unitario'], 2), 1);
                $pdf->Cell(40, 10, '$' . number_format($subtotal, 2), 1);
                $pdf->Ln();
            }
            
            // Total
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(150, 10, 'Total:', 1);
            $pdf->Cell(40, 10, '$' . number_format($total, 2), 1);
            
            // Enviar el PDF al navegador
            $pdf->Output('D', 'pedido-' . $pedidoId . '.pdf');
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
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

        // Automatizar envío del pedido a la cocina (imprimir ticket)
        try {
            $imprimirUrl = $_ENV['IMPRIMIR_PEDIDO_URL'] ?? null;
            if ($imprimirUrl) {
                $payload = json_encode(['id' => $pedidoId]);
                $opts = [
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $payload
                    ]
                ];
                $context = stream_context_create($opts);
                $result = file_get_contents($imprimirUrl, false, $context);
                // Opcional: log o procesar $result
            } else {
                error_log('IMPRIMIR_PEDIDO_URL no definido en .env');
            }
        } catch (Exception $e) {
            error_log('Error al enviar pedido a cocina: ' . $e->getMessage());
        }

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