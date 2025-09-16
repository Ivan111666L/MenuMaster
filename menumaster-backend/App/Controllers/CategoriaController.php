<?php
namespace App\Controllers;

use App\Models\Categoria;
use PDO;
use Exception;

class CategoriaController
{
    private $categoriaModel;

    public function __construct(PDO $db)
    {
        $this->categoriaModel = new Categoria($db);
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
        
        // Se envía la respuesta en el formato estándar
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $categorias]);
        exit;
    }
}