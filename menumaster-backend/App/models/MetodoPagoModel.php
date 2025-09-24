<?php
namespace App\Models;

class MetodoPagoModel {
    private $conn;
    private $table_name = "metodos_pago";

    public $id;
    public $nombre;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todos los métodos de pago
    public function read() {
        $query = "SELECT id, nombre FROM " . $this->table_name . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}