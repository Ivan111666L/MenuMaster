<?php

require_once __DIR__ . '/../Config/conexionDb.php';
use App\Config\conexionDb;

class PagoModel {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "pagos";

    // Propiedades del objeto
    public $id;
    public $pedido_id;
    public $monto;
    public $metodo_pago;
    public $usuario_id;
    public $fecha_pago;

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
     * Método para crear un nuevo registro de pago.
     * @return bool
     */
    public function crear() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      SET
                        pedido_id = :pedido_id,
                        monto = :monto,
                        metodo_pago = :metodo_pago,
                        usuario_id = :usuario_id";

            $stmt = $this->conn->prepare($query);

            // Sanitizar y vincular los parámetros
            $this->pedido_id = htmlspecialchars(strip_tags($this->pedido_id));
            $this->monto = htmlspecialchars(strip_tags($this->monto));
            $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
            $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));

            $stmt->bindParam(":pedido_id", $this->pedido_id, PDO::PARAM_INT);
            $stmt->bindParam(":monto", $this->monto);
            $stmt->bindParam(":metodo_pago", $this->metodo_pago);
            $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);

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
     * Método para leer todos los pagos.
     * @return array|false
     */
    public function leer() {
        try {
            $query = "SELECT id, pedido_id, monto, metodo_pago, usuario_id, fecha_pago
                      FROM " . $this->table_name . "
                      ORDER BY fecha_pago DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError("Error en leer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para leer los pagos de un pedido específico.
     * @param int $pedido_id
     * @return array|false
     */
    public function leerPorPedidoId($pedido_id) {
        try {
            $query = "SELECT id, pedido_id, monto, metodo_pago, usuario_id, fecha_pago
                      FROM " . $this->table_name . "
                      WHERE pedido_id = :pedido_id
                      ORDER BY fecha_pago DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":pedido_id", $pedido_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError("Error en leerPorPedidoId: " . $e->getMessage());
            return false;
        }
    }
}