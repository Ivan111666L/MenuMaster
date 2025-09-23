<?php
namespace App\Controllers;

use App\Utils\PrinterManager;
use App\Middleware\AuthMiddleware;

class PrinterController {
    private $printerManager;

    public function __construct() {
        $this->printerManager = new PrinterManager();
    }

    public function getConfig() {
        try {
            $config = $this->printerManager->getConfig();
            return json_encode([
                'status' => 'success',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateConfig() {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new \Exception('Datos de configuración no válidos');
            }
            
            // Validar datos mínimos requeridos
            if (!isset($data['ip']) || !isset($data['puerto'])) {
                throw new \Exception('La configuración debe incluir IP y puerto');
            }
            
            // Actualizar configuración
            $this->printerManager->updateConfig($data);
            
            return json_encode([
                'status' => 'success',
                'message' => 'Configuración actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function testConnection() {
        try {
            $result = $this->printerManager->testConnection();
            
            return json_encode([
                'status' => 'success',
                'message' => 'Conexión exitosa con la impresora',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Error de conexión: ' . $e->getMessage()
            ]);
        }
    }

    public function imprimirTest() {
        try {
            $this->printerManager->imprimirTextoTest();
            
            return json_encode([
                'status' => 'success',
                'message' => 'Texto de prueba enviado a la impresora'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Error al imprimir: ' . $e->getMessage()
            ]);
        }
    }
}