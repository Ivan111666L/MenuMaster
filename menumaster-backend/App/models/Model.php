<?php

namespace App\Models;

use PDO;

class Model {
    protected $db;
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct($db, $table = null) {
        $this->db = $db;
        $this->conn = $db; // Mantener compatibilidad con código existente
        if ($table) {
            $this->table = $table;
        }
    }

    // Obtener todos los registros
    public function all() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un registro por ID
    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Eliminar un registro por ID
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
