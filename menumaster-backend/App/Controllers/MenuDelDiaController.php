<?php
namespace App\Controllers;

// CORRECCIÓN: Se importa el nombre correcto del modelo: 'MenuDelDiaModel'.
use App\Models\MenuDelDia;
use PDO;
use Exception;

class MenuDelDiaController
{
    // CORRECCIÓN: Se usa un nombre de variable más claro para el modelo.
    private $MenuDelDia;

    public function __construct(PDO $db)
    {
        // CORRECCIÓN: Se instancia la clase 'MenuDelDiaModel', no el controlador mismo.
        $this->MenuDelDia = new MenuDelDia($db);
    }

    /**
     * Maneja la petición GET /api/menu-del-dia
     */
    public function getForToday(): void
    {
        // Se usa la variable de modelo corregida.
        $menu = $this->MenuDelDia->getForToday();
        if ($menu === false) {
            throw new Exception("No se pudo obtener el menú del día.", 500);
        }
        $this->sendResponse(200, $menu);
    }

    /**
     * Maneja la petición POST /api/menu-del-dia
     */
    public function add(array $data): void
    {
        if (empty($data['producto_id'])) {
            throw new Exception("El campo 'producto_id' es obligatorio.", 400);
        }
        
        $productoId = (int)$data['producto_id'];
        
        if (!$this->MenuDelDia->add($productoId)) {
            throw new Exception("No se pudo añadir el producto al menú.", 500);
        }
        
        $this->sendResponse(201, ['success' => true, 'message' => 'Producto añadido al menú del día.']);
    }

    /**
     * Maneja la petición DELETE /api/menu-del-dia/{productoId}
     */
    public function remove(int $productoId): void
    {
        if (!$this->MenuDelDia->remove($productoId)) {
            throw new Exception("No se pudo eliminar el producto del menú.", 500);
        }
        
        // 204 No Content es la respuesta estándar para un DELETE exitoso.
        $this->sendResponse(204, null);
    }
    
    // --- Helper para enviar respuestas (sin cambios, ya era correcto) ---
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }
}