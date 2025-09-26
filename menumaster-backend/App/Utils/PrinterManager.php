<?php
namespace App\Utils;

use Exception;

/**
 * Clase para gestionar la impresión de recibos y pedidos
 */
class PrinterManager
{
    private $printerIP;
    private $printerPort;
    private $printerName;
    private $isEnabled;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Cargar configuración desde archivo o base de datos
        $this->loadConfig();
    }
    
    /**
     * Carga la configuración de la impresora
     */
    private function loadConfig()
    {
        // Por defecto, usar configuración de archivo
        $configFile = __DIR__ . '/../../config/printer.json';
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            $this->printerIP = $config['ip'] ?? '127.0.0.1';
            $this->printerPort = $config['port'] ?? 9100;
            $this->printerName = $config['name'] ?? 'Impresora Térmica';
            $this->isEnabled = $config['enabled'] ?? false;
        } else {
            // Valores por defecto si no existe el archivo
            $this->printerIP = '127.0.0.1';
            $this->printerPort = 9100;
            $this->printerName = 'Impresora Térmica';
            $this->isEnabled = false;
            
            // Crear archivo de configuración por defecto
            $this->saveConfig();
        }
    }
    
    /**
     * Guarda la configuración de la impresora
     */
    public function saveConfig()
    {
        $config = [
            'ip' => $this->printerIP,
            'port' => $this->printerPort,
            'name' => $this->printerName,
            'enabled' => $this->isEnabled
        ];
        
        $configFile = __DIR__ . '/../../config/printer.json';
        
        // Asegurar que el directorio existe
        if (!is_dir(dirname($configFile))) {
            mkdir(dirname($configFile), 0755, true);
        }
        
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        return true;
    }
    
    /**
     * Obtiene la configuración actual de la impresora
     */
    public function getConfig()
    {
        return [
            'ip' => $this->printerIP,
            'port' => $this->printerPort,
            'name' => $this->printerName,
            'enabled' => $this->isEnabled
        ];
    }
    
    /**
     * Actualiza la configuración de la impresora
     */
    public function updateConfig(array $config)
    {
        if (isset($config['ip'])) $this->printerIP = $config['ip'];
        if (isset($config['port'])) $this->printerPort = (int)$config['port'];
        if (isset($config['name'])) $this->printerName = $config['name'];
        if (isset($config['enabled'])) $this->isEnabled = (bool)$config['enabled'];
        
        return $this->saveConfig();
    }
    
    /**
     * Imprime un pedido
     */
    public function printOrder(array $pedido)
    {
        try {
            $content = $this->formatOrderContent($pedido);
            return $this->sendToPrinter($content);
        } catch (Exception $e) {
            error_log('Error al imprimir pedido: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Imprime un recibo
     */
    public function printReceipt(array $pedido)
    {
        if (!$this->isEnabled) {
            return false;
        }
        
        try {
            $content = $this->formatReceiptContent($pedido);
            return $this->sendToPrinter($content);
        } catch (Exception $e) {
            error_log('Error al imprimir recibo: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prueba la conexión con la impresora
     */
    // public function testConnection()
    // {
    //     if (!$this->isEnabled) {
    //         throw new Exception("La impresora está desactivada en la configuración");
    //     }
        
    //     $socket = @fsockopen($this->printerIP, $this->printerPort, $errno, $errstr, 5);
    //     if (!$socket) {
    //         throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
    //     }
        
    //     fclose($socket);
    //     return true;
    // }
    
    /**
     * Imprime un texto de prueba
     */
    public function imprimirTextoTest()
    {
        if (!$this->isEnabled) {
            throw new Exception("La impresora está desactivada en la configuración");
        }
        
        $content = "\n\n";
        $content .= "===== PRUEBA DE IMPRESIÓN =====\n\n";
        $content .= "Impresora: " . $this->printerName . "\n";
        $content .= "IP: " . $this->printerIP . "\n";
        $content .= "Puerto: " . $this->printerPort . "\n";
        $content .= "Fecha: " . date('d/m/Y H:i:s') . "\n\n";
        $content .= "Si puede leer este mensaje, la impresora\n";
        $content .= "está configurada correctamente.\n\n";
        $content .= "==============================\n\n\n\n";
        
        return $this->sendToPrinter($content);
    }
    
    /**
     * Formatea el contenido del pedido para impresión
     */
    private function formatOrderContent(array $pedido): string
    {
        $output = "\n";
        $output .= "===== PEDIDO #" . $pedido['id'] . " =====\n\n";
        $output .= "Mesa: " . $pedido['mesa_nombre'] . "\n";
        $output .= "Mesero: " . $pedido['usuario_nombre'] . "\n";
        $output .= "Fecha: " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "\n\n";
        
        $output .= "PRODUCTOS:\n";
        $output .= "--------------------------------\n";
        
        foreach ($pedido['items'] as $item) {
            if (isset($item['es_combo']) && $item['es_combo']) {
                $output .= $item['cantidad'] . "x " . $item['nombre'] . " (COMBO)\n";
                
                // Mostrar elementos del combo
                if (isset($item['elementos']) && is_array($item['elementos'])) {
                    foreach ($item['elementos'] as $elemento) {
                        $output .= "   - " . $elemento['cantidad'] . "x " . $elemento['nombre'] . "\n";
                    }
                }
            } else {
                $output .= $item['cantidad'] . "x " . $item['nombre'] . "\n";
            }
            
            // Agregar notas si existen
            if (!empty($item['notas'])) {
                $output .= "   Nota: " . $item['notas'] . "\n";
            }
        }
        
        // Agregar notas generales del pedido
        if (!empty($pedido['notas'])) {
            $output .= "\nNOTAS GENERALES:\n";
            $output .= $pedido['notas'] . "\n";
        }
        
        $output .= "\n================================\n\n\n\n";
        
        return $output;
    }
    
    /**
     * Formatea el contenido del recibo para impresión
     */
    /**
     * Envía contenido a la impresora
     */
    // private function sendToPrinter(string $content): bool
    // {
    //     if (!$this->isEnabled) {
    //         return false;
    //     }
        
    //     $socket = @fsockopen($this->printerIP, $this->printerPort, $errno, $errstr, 5);
    //     if (!$socket) {
    //         throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
    //     }
        
    //     fwrite($socket, $content);
    //     fclose($socket);
    //     return true;
    // }
    
    private function formatReceiptContent(array $pedido): string
    {
        $output = "\n";
        $output .= "===== MENU MASTER =====\n";
        $output .= "RECIBO DE VENTA\n\n";
        $output .= "Pedido #: " . $pedido['id'] . "\n";
        $output .= "Mesa: " . $pedido['mesa_nombre'] . "\n";
        $output .= "Mesero: " . $pedido['usuario_nombre'] . "\n";
        $output .= "Fecha: " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "\n\n";
        
        $output .= "DETALLE:\n";
        $output .= "--------------------------------\n";
        
        $total = 0;
        
        foreach ($pedido['items'] as $item) {
            $subtotal = $item['precio_unitario'] * $item['cantidad'];
            $total += $subtotal;
            
            if (isset($item['es_combo']) && $item['es_combo']) {
                $output .= $item['cantidad'] . "x " . $item['nombre'] . " (COMBO)\n";
                $output .= "   $" . number_format($item['precio_unitario'], 2) . " x " . $item['cantidad'] . " = $" . number_format($subtotal, 2) . "\n";
                
                // Mostrar elementos del combo
                if (isset($item['elementos']) && is_array($item['elementos'])) {
                    foreach ($item['elementos'] as $elemento) {
                        $output .= "   - " . $elemento['cantidad'] . "x " . $elemento['nombre'] . "\n";
                    }
                }
            } else {
                $output .= $item['cantidad'] . "x " . $item['nombre'] . "\n";
                $output .= "   $" . number_format($item['precio_unitario'], 2) . " x " . $item['cantidad'] . " = $" . number_format($subtotal, 2) . "\n";
            }
        }
        
        $output .= "--------------------------------\n";
        $output .= "TOTAL: $" . number_format($total, 2) . "\n\n";
        
        // Información de pago
        if (isset($pedido['pago'])) {
            $output .= "Método de pago: " . $pedido['pago']['metodo'] . "\n";
            if (isset($pedido['pago']['referencia']) && !empty($pedido['pago']['referencia'])) {
                $output .= "Referencia: " . $pedido['pago']['referencia'] . "\n";
            }
        }
        
        $output .= "\n¡Gracias por su visita!\n";
        $output .= "================================\n\n\n\n";
        
        return $output;
    }
    
    /**
     * Envía el contenido a la impresora
     */
    private function sendToPrinter(string $content): bool
    {
        // En un entorno de producción, aquí se conectaría a la impresora
        // Para esta implementación, simularemos la impresión
        
        if ($this->isEnabled) {
            try {
                // Intentar conectar a la impresora
                $socket = @fsockopen($this->printerIP, $this->printerPort, $errno, $errstr, 10);
                
                if (!$socket) {
                    error_log("Error de conexión a la impresora: $errstr ($errno)");
                    return false;
                }
                
                // Enviar contenido a la impresora
                fwrite($socket, $content);
                fclose($socket);
                
                return true;
            } catch (Exception $e) {
                error_log('Error al enviar a la impresora: ' . $e->getMessage());
                return false;
            }
        }
        
        // Si la impresión está deshabilitada, solo registrar el contenido
        error_log('Simulación de impresión: ' . str_replace("\n", "\\n", $content));
        return true;
    }
    
    /**
     * Verifica si la impresora está disponible
     */
    public function testConnection(): bool
    {
        if (!$this->isEnabled) {
            return false;
        }
        
        try {
            $socket = @fsockopen($this->printerIP, $this->printerPort, $errno, $errstr, 5);
            
            if (!$socket) {
                return false;
            }
            
            fclose($socket);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}