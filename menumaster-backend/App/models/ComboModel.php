<?php

namespace App\Models;

use PDO;
use App\Config\ConexionDb;

class ComboModel {
    private $db;

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            try {
                $this->db = ConexionDb::getInstance()->getConnection();
            } catch (\Exception $e) {
                // Handle database connection error
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Obtener todos los combos
     */
    public function findAll($includeElements = false) {
        $query = "SELECT * FROM combos WHERE estado_id = 1 ORDER BY destacado DESC, nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($includeElements && !empty($combos)) {
            foreach ($combos as &$combo) {
                $combo['elementos'] = $this->getComboElements($combo['id']);
            }
        }

        return $combos;
    }

    /**
     * Obtener un combo por su ID
     */
    public function findById($id, $includeElements = true) {
        $query = "SELECT * FROM combos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $combo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($combo && $includeElements) {
            $combo['elementos'] = $this->getComboElements($id);
        }

        return $combo;
    }

    /**
     * Obtener los elementos de un combo
     */
    public function getComboElements($comboId) {
        $query = "
            SELECT ce.*, p.nombre as producto_nombre, p.precio as producto_precio, p.imagen_url 
            FROM combo_elementos ce
            JOIN productos p ON ce.producto_id = p.id
            WHERE ce.combo_id = :combo_id
            ORDER BY ce.opcional ASC, p.nombre ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':combo_id', $comboId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo combo
     */
    public function create($data) {
        $query = "
            INSERT INTO combos (nombre, descripcion, precio, descuento, imagen_url, destacado)
            VALUES (:nombre, :descripcion, :precio, :descuento, :imagen_url, :destacado)
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);
        $stmt->bindParam(':precio', $data['precio'], PDO::PARAM_STR);
        $stmt->bindParam(':descuento', $data['descuento'], PDO::PARAM_STR);
        $stmt->bindParam(':imagen_url', $data['imagen_url'], PDO::PARAM_STR);
        $stmt->bindParam(':destacado', $data['destacado'], PDO::PARAM_INT);
        $stmt->execute();

        $comboId = $this->db->lastInsertId();

        // Si hay elementos, los agregamos
        if (!empty($data['elementos'])) {
            foreach ($data['elementos'] as $elemento) {
                $this->addComboElement($comboId, $elemento);
            }
        }

        return $comboId;
    }

    /**
     * Agregar un elemento al combo
     */
    public function addComboElement($comboId, $elementoData) {
        $query = "
            INSERT INTO combo_elementos (combo_id, producto_id, cantidad, opcional)
            VALUES (:combo_id, :producto_id, :cantidad, :opcional)
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':combo_id', $comboId, PDO::PARAM_INT);
        $stmt->bindParam(':producto_id', $elementoData['producto_id'], PDO::PARAM_INT);
        $stmt->bindParam(':cantidad', $elementoData['cantidad'], PDO::PARAM_INT);
        $stmt->bindParam(':opcional', $elementoData['opcional'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualizar un combo existente
     */
    public function update($id, $data) {
        $query = "
            UPDATE combos 
            SET nombre = :nombre, 
                descripcion = :descripcion, 
                precio = :precio, 
                descuento = :descuento, 
                imagen_url = :imagen_url, 
                destacado = :destacado,
                estado_id = :estado_id
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);
        $stmt->bindParam(':precio', $data['precio'], PDO::PARAM_STR);
        $stmt->bindParam(':descuento', $data['descuento'], PDO::PARAM_STR);
        $stmt->bindParam(':imagen_url', $data['imagen_url'], PDO::PARAM_STR);
        $stmt->bindParam(':destacado', $data['destacado'], PDO::PARAM_INT);
        $stmt->bindParam(':estado_id', $data['estado_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();

        // Si hay elementos, actualizamos
        if (!empty($data['elementos'])) {
            // Primero eliminamos los elementos actuales
            $this->deleteComboElements($id);
            
            // Luego agregamos los nuevos
            foreach ($data['elementos'] as $elemento) {
                $this->addComboElement($id, $elemento);
            }
        }

        return $result;
    }

    /**
     * Eliminar los elementos de un combo
     */
    public function deleteComboElements($comboId) {
        $query = "DELETE FROM combo_elementos WHERE combo_id = :combo_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':combo_id', $comboId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Eliminar un combo
     */
    public function delete($id) {
        // Primero eliminamos los elementos
        $this->deleteComboElements($id);
        
        // Luego eliminamos el combo
        $query = "DELETE FROM combos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Cambiar el estado de un combo (activar/desactivar)
     */
    public function changeStatus($id, $estadoId) {
        $query = "UPDATE combos SET estado_id = :estado_id WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estado_id', $estadoId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
