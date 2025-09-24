<?php
namespace App\Utils;

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/vendor/setasign/fpdf/fpdf.php';

class ReportePDF extends \FPDF {
    private $titulo;
    private $fechaInicio;
    private $fechaFin;
    
    public function __construct($titulo, $fechaInicio, $fechaFin) {
        parent::__construct();
        $this->titulo = $titulo;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->SetMargins(10, 10, 10);
        $this->AddPage();
        $this->SetFont('Arial', '', 10);
    }
    
    public function Header() {
        // Logo
        $this->Image(BASE_PATH . '/public/assets/img/logo.png', 10, 8, 30);
        // Título
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, $this->titulo, 0, 1, 'C');
        // Período
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 6, 'Período: ' . $this->fechaInicio . ' al ' . $this->fechaFin, 0, 1, 'C');
        // Fecha de generación
        $this->Cell(0, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Ln(5);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    public function agregarSeccionMeseros($datos) {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'ANÁLISIS DE MESEROS', 0, 1, 'L');
        $this->Ln(2);
        
        // Tabla de meseros
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(60, 7, 'Mesero', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Pedidos', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Ventas Totales', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Promedio/Pedido', 1, 1, 'C', true);
        
        $this->SetFont('Arial', '', 10);
        foreach ($datos as $mesero) {
            $this->Cell(60, 6, utf8_decode($mesero['nombre']), 1, 0, 'L');
            $this->Cell(40, 6, $mesero['total_pedidos'], 1, 0, 'C');
            $this->Cell(40, 6, '$' . number_format($mesero['ventas_totales'], 2), 1, 0, 'R');
            $this->Cell(40, 6, '$' . number_format($mesero['promedio_pedido'], 2), 1, 1, 'R');
        }
        
        // Gráfico de barras simple
        $this->Ln(10);
        $this->Cell(0, 10, 'Gráfico de Ventas por Mesero', 0, 1, 'C');
        
        $maxVenta = 0;
        foreach ($datos as $mesero) {
            if ($mesero['ventas_totales'] > $maxVenta) {
                $maxVenta = $mesero['ventas_totales'];
            }
        }
        
        $y = $this->GetY();
        $barWidth = 30;
        $maxBarHeight = 50;
        $x = 30;
        
        foreach ($datos as $mesero) {
            $barHeight = ($mesero['ventas_totales'] / $maxVenta) * $maxBarHeight;
            $this->SetFillColor(100, 149, 237); // Azul claro
            $this->Rect($x, $y, $barWidth, -$barHeight, 'F');
            $this->SetXY($x, $y + 5);
            $this->Cell($barWidth, 5, utf8_decode(substr($mesero['nombre'], 0, 10)), 0, 0, 'C');
            $this->SetXY($x, $y - $barHeight - 5);
            $this->Cell($barWidth, 5, '$' . number_format($mesero['ventas_totales'], 0), 0, 0, 'C');
            $x += $barWidth + 10;
        }
        
        $this->SetY($y + 15);
    }
    
    public function agregarSeccionProductos($datos) {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'ANÁLISIS DE PRODUCTOS', 0, 1, 'L');
        $this->Ln(2);
        
        // Tabla de productos
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(70, 7, 'Producto', 1, 0, 'C', true);
        $this->Cell(30, 7, 'Cantidad', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Precio Unitario', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Ventas Totales', 1, 1, 'C', true);
        
        $this->SetFont('Arial', '', 10);
        foreach ($datos as $producto) {
            $this->Cell(70, 6, utf8_decode($producto['nombre']), 1, 0, 'L');
            $this->Cell(30, 6, $producto['cantidad_total'], 1, 0, 'C');
            $this->Cell(40, 6, '$' . number_format($producto['precio_unitario'], 2), 1, 0, 'R');
            $this->Cell(40, 6, '$' . number_format($producto['ventas_totales'], 2), 1, 1, 'R');
        }
        
        // Top 5 productos más vendidos
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'TOP 5 PRODUCTOS MÁS VENDIDOS', 0, 1, 'L');
        
        // Ordenar por cantidad
        usort($datos, function($a, $b) {
            return $b['cantidad_total'] - $a['cantidad_total'];
        });
        
        $top5 = array_slice($datos, 0, 5);
        
        $y = $this->GetY();
        $barWidth = 30;
        $maxBarHeight = 50;
        $x = 30;
        
        $maxCantidad = 0;
        foreach ($top5 as $producto) {
            if ($producto['cantidad_total'] > $maxCantidad) {
                $maxCantidad = $producto['cantidad_total'];
            }
        }
        
        foreach ($top5 as $producto) {
            $barHeight = ($producto['cantidad_total'] / $maxCantidad) * $maxBarHeight;
            $this->SetFillColor(152, 251, 152); // Verde claro
            $this->Rect($x, $y, $barWidth, -$barHeight, 'F');
            $this->SetXY($x, $y + 5);
            $this->Cell($barWidth, 5, utf8_decode(substr($producto['nombre'], 0, 10)), 0, 0, 'C');
            $this->SetXY($x, $y - $barHeight - 5);
            $this->Cell($barWidth, 5, $producto['cantidad_total'] . ' uds', 0, 0, 'C');
            $x += $barWidth + 10;
        }
    }
    
    public function agregarResumenVentas($datos) {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'RESUMEN DE VENTAS', 0, 1, 'L');
        $this->Ln(2);
        
        // Información general
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 8, 'Total de Pedidos:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(80, 8, $datos['total_pedidos'], 0, 1);
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 8, 'Ventas Totales:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(80, 8, '$' . number_format($datos['ventas_totales'], 2), 0, 1);
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 8, 'Promedio por Pedido:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(80, 8, '$' . number_format($datos['promedio_pedido'], 2), 0, 1);
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 8, 'Día con Más Ventas:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(80, 8, $datos['dia_mas_ventas'] . ' ($' . number_format($datos['monto_dia_mas_ventas'], 2) . ')', 0, 1);
        
        $this->Ln(10);
        
        // Ventas diarias
        if (isset($datos['ventas_diarias']) && !empty($datos['ventas_diarias'])) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'VENTAS DIARIAS', 0, 1, 'L');
            
            $this->SetFillColor(230, 230, 230);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(60, 7, 'Fecha', 1, 0, 'C', true);
            $this->Cell(40, 7, 'Pedidos', 1, 0, 'C', true);
            $this->Cell(40, 7, 'Ventas', 1, 1, 'C', true);
            
            $this->SetFont('Arial', '', 10);
            foreach ($datos['ventas_diarias'] as $dia) {
                $this->Cell(60, 6, $dia['fecha'], 1, 0, 'L');
                $this->Cell(40, 6, $dia['pedidos'], 1, 0, 'C');
                $this->Cell(40, 6, '$' . number_format($dia['ventas'], 2), 1, 1, 'R');
            }
        }
        $this->Output();
    }
}