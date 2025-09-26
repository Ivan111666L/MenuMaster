<?php
namespace App\Models;

use PDO;
use PDOException;

class EstadoProductoModel 
{
    private $conn;
    private $table_name = "estados_producto";

    public function __construct(PDO $db) 
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los estados de producto.
     * @return array|false Un array de estados o false si hay un error.
     */
    public function findAll(): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table_name} ORDER BY id ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            // ✅ CORRECCIÓN: Devolvemos el array de resultados, no el objeto de la sentencia.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en EstadoProductoModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✨ MÉTODO AÑADIDO: Obtiene un estado por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table_name} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en EstadoProductoModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✨ MÉTODO AÑADIDO: Obtiene un estado por su nombre.
     * Esencial para que el ProductoController pueda convertir un nombre como "disponible" en un ID.
     * @param string $name
     * @return array|false
     */
    public function findByName(string $name): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table_name} WHERE nombre = :nombre";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $name);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en EstadoProductoModel::findByName: ' . $e->getMessage());
            return false;
        }
    }
}
