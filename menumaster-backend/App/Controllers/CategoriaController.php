<?php
namespace App\Controllers;

use app\Models\CategoriaModel;
use PDO;
use Exception;

class CategoriaController
{
    private $categoriaModel;

    public function __construct(PDO $db)
    {
        $this->categoriaModel = new CategoriaModel($db);
    }

    /**
     * Maneja la petición GET /api/categorias
     */
    public function index(): void
    {
        $categorias = $this->categoriaModel->findAll();
        
        if ($categorias === false) {
            throw new Exception("No se pudieron obtener las categorías.", 500);
        }
        
        // CORRECCIÓN: Se usa el método helper 'sendResponse' para consistencia.
        $this->sendResponse(200, $categorias);
    }

    /**
     * Envía la respuesta HTTP en formato JSON y termina la ejecución.
     */
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}