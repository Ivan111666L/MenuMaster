<?php

namespace App\Controllers;

use App\Models\ComboModel;
use App\Utils\ResponseUtil;

class ComboController {
    private $comboModel;
    
    public function __construct() {
        $this->comboModel = new ComboModel();
    }
    
    /**
     * Obtener todos los combos
     */
    public function getCombos() {
        try {
            $includeElements = isset($_GET['includeElements']) && $_GET['includeElements'] === 'true';
            $combos = $this->comboModel->findAll($includeElements);
            return ResponseUtil::success($combos);
        } catch (\Exception $e) {
            return ResponseUtil::error($e->getMessage());
        }
    }
    
    /**
     * Obtener un combo por su ID
     */
    public function getCombo($id) {
        try {
            $combo = $this->comboModel->findById($id);
            if (!$combo) {
                return ResponseUtil::notFound('Combo no encontrado');
            }
            return ResponseUtil::success($combo);
        } catch (\Exception $e) {
            return ResponseUtil::error($e->getMessage());
        }
    }
    
    /**
     * Crear un nuevo combo
     */
    public function createCombo() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos
            if (empty($data['nombre']) || empty($data['precio'])) {
                return ResponseUtil::badRequest('Nombre y precio son obligatorios');
            }
            
            // Valores por defecto
            $data['descuento'] = $data['descuento'] ?? 0;
            $data['destacado'] = $data['destacado'] ?? 0;
            
            $comboId = $this->comboModel->create($data);
            return ResponseUtil::success(['id' => $comboId, 'message' => 'Combo creado con éxito']);
        } catch (\Exception $e) {
            return ResponseUtil::error($e->getMessage());
        }
    }
    
    /**
     * Actualizar un combo existente
     */
    public function updateCombo($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Verificar que el combo existe
            $combo = $this->comboModel->findById($id, false);
            if (!$combo) {
                return ResponseUtil::notFound('Combo no encontrado');
            }
            
            // Validar datos
            if (empty($data['nombre']) || empty($data['precio'])) {
                return ResponseUtil::badRequest('Nombre y precio son obligatorios');
            }
            
            // Valores por defecto
            $data['descuento'] = $data['descuento'] ?? 0;
            $data['destacado'] = $data['destacado'] ?? 0;
            $data['estado_id'] = $data['estado_id'] ?? 1;
            
            $result = $this->comboModel->update($id, $data);
            return ResponseUtil::success(['message' => 'Combo actualizado con éxito']);
        } catch (\Exception $e) {
            return ResponseUtil::error($e->getMessage());
        }
    }
    
    /**
     * Eliminar un combo
     */
    public function deleteCombo($id) {
        try {
            // Verificar que el combo existe
            $combo = $this->comboModel->findById($id, false);
            if (!$combo) {
                return ResponseUtil::notFound('Combo no encontrado');
            }
            
            $result = $this->comboModel->delete($id);
            return ResponseUtil::success(['message' => 'Combo eliminado con éxito']);
        } catch (\Exception $e) {
            return ResponseUtil::error($e->getMessage());
        }
    }
    
    /**
     * Cambiar el estado de un combo
     */
    public function changeComboStatus($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Verificar que el combo existe
            $combo = $this->comboModel->findById($id, false);
            if (!$combo) {
                return ResponseUtil::notFound('Combo no encontrado');
            }
            
            // Validar datos
            if (!isset($data['estado_id'])) {
                return ResponseUtil::badRequest('Estado es obligatorio');
            }
            
            $result = $this->comboModel->changeStatus($id, $data['estado_id']);
            return ResponseUtil::success(['message' => 'Estado del combo actualizado con éxito']);
        } catch (\Exception $e) {
            return ResponseUtil::error($e->getMessage());
        }
    }
}