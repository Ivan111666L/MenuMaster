<?php
namespace App\Controllers;

use Exception;
use App\Services\DIANService;
use App\Services\MailerService;
use App\Models\PedidoModel;

class FacturacionElectronicaController
{
    private $db;
    private $dianService;
    private $mailer;
    private $pedidoModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->dianService = new DIANService();
        $this->mailer = new MailerService();
        $this->pedidoModel = new PedidoModel($db);
    }

    public function emitirFactura($data)
    {
        $pedidoId = isset($data['pedido_id']) ? (int)$data['pedido_id'] : null;
        $email    = $data['email'] ?? null;
        if (!$pedidoId) {
            throw new Exception('pedido_id es requerido', 400);
        }

        // 1) Obtener pedido y sus items
        $pedido = $this->pedidoModel->obtenerPedidoConItems($pedidoId);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        // 2) Generar UBL XML + CUFE/CUDE (stub) + referencia de numeración
        $resultado = $this->dianService->generarFacturaElectronica($pedido);

        // 3) Persistir estado de facturación (stub: marcar facturado electronico)
        $this->pedidoModel->marcarFacturadoElectronico($pedidoId, $resultado['cufe'] ?? null, $resultado['numero'] ?? null);

        // 4) Enviar por correo si se solicitó (tolerante a entorno sin PHPMailer)
        if ($email) {
            try {
                if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                    $this->mailer->enviarComprobante($email, $resultado['xml_path'], $resultado['pdf_path'] ?? null);
                } else {
                    error_log('PHPMailer no disponible, se omite el envío de correo.');
                }
            } catch (\Throwable $t) {
                error_log('Fallo envío de comprobante: ' . $t->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'pedido_id' => $pedidoId,
                'estado' => 'emitida',
                'cufe' => $resultado['cufe'] ?? null,
                'numero' => $resultado['numero'] ?? null,
                'xml' => $resultado['xml_path'] ?? null,
                'pdf' => $resultado['pdf_path'] ?? null,
            ]
        ]);
    }

    public function enviarFacturaPorCorreo($data)
    {
        $pedidoId = isset($data['pedido_id']) ? (int)$data['pedido_id'] : null;
        $email    = $data['email'] ?? null;
        if (!$pedidoId || !$email) {
            throw new Exception('pedido_id y email son requeridos', 400);
        }

        $pedido = $this->pedidoModel->obtenerPedidoConItems($pedidoId);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        $paths = $this->dianService->obtenerRutasComprobante($pedido);
        $this->mailer->enviarComprobante($email, $paths['xml_path'] ?? null, $paths['pdf_path'] ?? null);

        echo json_encode([
            'success' => true,
            'data' => [ 'pedido_id' => $pedidoId, 'email' => $email, 'enviado' => true ]
        ]);
    }

    public function obtenerEstadoFactura($pedidoId)
    {
        if (!$pedidoId) {
            throw new Exception('pedido_id es requerido', 400);
        }
        $estado = $this->pedidoModel->obtenerEstadoFacturacionElectronica($pedidoId);
        echo json_encode([
            'success' => true,
            'data' => $estado
        ]);
    }
}