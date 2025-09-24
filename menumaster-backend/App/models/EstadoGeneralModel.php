<?php
namespace App\Models;

class EstadoGeneralModel {
    private $conn;
    private $table_name = "estados_generales";

    public $id;
    public $nombre;
    public $tipo; // 'producto' o 'pedido'

    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todos los estados generales
    public function read() {
        $query = "SELECT id, nombre, tipo FROM " . $this->table_name . " ORDER BY tipo, nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function findByName(string $name) {
        $query = "SELECT id, nombre, tipo FROM " . $this->table_name . " WHERE nombre = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->execute();
        return $stmt;
    }
}