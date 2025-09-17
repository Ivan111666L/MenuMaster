<?php
require_once __DIR__ . '/../config/conexionDb.php';
use app\config\conexonDb;

class ProductoIngredientesModel {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "producto_ingredientes";

    // Propiedades del objeto
    public $id;
    public $producto_id;
    public $ingrediente_id;
    public $cantidad;

    // Constructor con la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Método para registrar errores en un archivo de log.
     * @param string $mensaje
     */
    private function logError($mensaje) {
        $logFile = __DIR__ . '/../logs/error.log';
        $fecha = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$fecha] $mensaje\n", FILE_APPEND);
    }

    /**
     * Método para crear una nueva relación entre producto e ingrediente.
     * @return bool
     */
    public function crear() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      SET
                        producto_id = :producto_id,
                        ingrediente_id = :ingrediente_id,
                        cantidad = :cantidad";

            $stmt = $this->conn->prepare($query);

            // Sanitizar y vincular los parámetros
            $this->producto_id = htmlspecialchars(strip_tags($this->producto_id));
            $this->ingrediente_id = htmlspecialchars(strip_tags($this->ingrediente_id));
            $this->cantidad = htmlspecialchars(strip_tags($this->cantidad));

            $stmt->bindParam(":producto_id", $this->producto_id);
            $stmt->bindParam(":ingrediente_id", $this->ingrediente_id);
            $stmt->bindParam(":cantidad", $this->cantidad);

            if ($stmt->execute()) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logError("Error en crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para leer todos los ingredientes de un producto específico.
     * @return PDOStatement|false
     */
    public function leerPorProductoId() {
        try {
            $query = "SELECT
                        pi.id, pi.producto_id, pi.ingrediente_id, pi.cantidad,
                        i.nombre as ingrediente_nombre, i.unidad_medida
                      FROM " . $this->table_name . " pi
                      LEFT JOIN
                        ingredientes i ON pi.ingrediente_id = i.id
                      WHERE
                        pi.producto_id = ?
                      ORDER BY i.nombre ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->producto_id);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Error en leerPorProductoId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para eliminar las relaciones de un producto.
     * @return bool
     */
    public function eliminarPorProductoId() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE producto_id = ?";
            $stmt = $this->conn->prepare($query);

            // Sanitizar
            $this->producto_id = htmlspecialchars(strip_tags($this->producto_id));
            $stmt->bindParam(1, $this->producto_id);

            if ($stmt->execute()) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logError("Error en eliminarPorProductoId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para eliminar las relaciones de un ingrediente.
     * @return bool
     */
    public function eliminarPorIngredienteId() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE ingrediente_id = ?";
            $stmt = $this->conn->prepare($query);

            // Sanitizar
            $this->ingrediente_id = htmlspecialchars(strip_tags($this->ingrediente_id));
            $stmt->bindParam(1, $this->ingrediente_id);

            if ($stmt->execute()) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logError("Error en eliminarPorIngredienteId: " . $e->getMessage());
            return false;
        }
    }
}