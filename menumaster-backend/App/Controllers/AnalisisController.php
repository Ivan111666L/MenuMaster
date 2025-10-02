<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use PDO;
use Exception;

class AnalisisController
{
    private $pedidoModel;
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->pedidoModel = new PedidoModel($this->db);
    }

    /**
     * GET /api/analisis/ventas
     * Obtiene estadísticas de ventas para el dashboard
     */
    public function getEstadisticasVentas()
    {
        try {
            // Obtener parámetros de fecha desde la URL
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $estadisticas = $this->pedidoModel->getEstadisticasVentas($fechaInicio, $fechaFin);
            
            $this->sendResponse(200, $estadisticas);
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/analisis/meseros
     * Obtiene estadísticas específicas de meseros
     */
    public function getEstadisticasMeseros()
    {
        try {
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $estadisticas = $this->pedidoModel->getEstadisticasVentas($fechaInicio, $fechaFin);
            
            $this->sendResponse(200, $estadisticas['meseros']);
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/analisis/productos
     * Obtiene estadísticas específicas de productos
     */
    public function getEstadisticasProductos()
    {
        try {
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $estadisticas = $this->pedidoModel->getEstadisticasVentas($fechaInicio, $fechaFin);
            
            $this->sendResponse(200, $estadisticas['productos']);
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/analisis/pdf
     * Genera un PDF con el análisis de datos
     */
    public function generarPDF()
    {
        try {
            $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            $estadisticas = $this->pedidoModel->getEstadisticasVentas($fechaInicio, $fechaFin);
            
            // Generar el PDF con FPDF
            $fpdfPath = BASE_PATH . '/vendor/setasign/fpdf/fpdf.php';
            if (file_exists($fpdfPath)) {
                require_once $fpdfPath;
            } else {
                // Fallback: try alternative FPDF path or create a simple response
                throw new Exception('FPDF library not found. Please install via: composer require setasign/fpdf');
            }
            
            // First install FPDF via Composer:
            // composer require setasign/fpdf
            if (!class_exists('FPDF')) {
                throw new Exception('FPDF class not available. Please install FPDF library.');
            }
            
            $pdf = new \FPDF();
            $pdf->AddPage();
            
            // Título
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Informe de Ventas - MenuMaster', 0, 1, 'C');
            $pdf->Cell(0, 10, 'Periodo: ' . ($fechaInicio ?? 'Último mes') . ' al ' . ($fechaFin ?? 'Hoy'), 0, 1, 'C');
            $pdf->Ln(10);
            
            // Sección 1: Meseros con más ventas
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Meseros con Mayor Volumen de Ventas', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            $pdf->Cell(60, 7, 'Mesero', 1, 0, 'C');
            $pdf->Cell(40, 7, 'Pedidos', 1, 0, 'C');
            $pdf->Cell(40, 7, 'Total Ventas', 1, 1, 'C');
            
            foreach ($estadisticas['meseros'] as $mesero) {
                $pdf->Cell(60, 7, $mesero['usuario_nombre'], 1, 0, 'L');
                $pdf->Cell(40, 7, $mesero['total_pedidos'], 1, 0, 'C');
                $pdf->Cell(40, 7, '$' . number_format($mesero['total_ventas'], 2), 1, 1, 'R');
            }
            
            $pdf->Ln(10);
            
            // Sección 2: Productos más vendidos
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Productos Más Vendidos', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            $pdf->Cell(80, 7, 'Producto', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Cantidad', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Ingresos', 1, 1, 'C');
            
            foreach ($estadisticas['productos'] as $producto) {
                $pdf->Cell(80, 7, $producto['producto_nombre'], 1, 0, 'L');
                $pdf->Cell(30, 7, $producto['total_vendido'], 1, 0, 'C');
                $pdf->Cell(30, 7, '$' . number_format($producto['total_ingresos'], 2), 1, 1, 'R');
            }
            
            $pdf->Ln(10);
            
            // Sección 3: Ventas diarias
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Ventas Diarias', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            
            $pdf->Cell(50, 7, 'Fecha', 1, 0, 'C');
            $pdf->Cell(40, 7, 'Pedidos', 1, 0, 'C');
            $pdf->Cell(50, 7, 'Total Ventas', 1, 1, 'C');
            
            foreach ($estadisticas['ventas_diarias'] as $venta) {
                $pdf->Cell(50, 7, $venta['fecha'], 1, 0, 'L');
                $pdf->Cell(40, 7, $venta['total_pedidos'], 1, 0, 'C');
                $pdf->Cell(50, 7, '$' . number_format($venta['total_ventas'], 2), 1, 1, 'R');
            }
            
            // Salida del PDF
            $pdfOutput = $pdf->Output('S');
            
            // Enviar el PDF como respuesta
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="informe_ventas.pdf"');
            echo $pdfOutput;
            exit;
            
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
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