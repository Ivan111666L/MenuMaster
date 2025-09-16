<?php
namespace App\Models;

use PDO;
use PDOException;

class EstadoMesa 
{
    private $conn;
    private $table = 'estados_mesa';

    public function __construct(PDO $db) 
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los estados de mesa.
     * @return array|false Un array de estados o false si hay un error.
     */
    public function findAll(): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table} ORDER BY id ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en EstadoMesaModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un estado por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            // ✅ CORRECCIÓN: Se elimina la sanitización manual innecesaria.
            // bindParam con PDO::PARAM_INT ya es seguro.
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en EstadoMesaModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✨ MÉTODO AÑADIDO: Obtiene un estado por su nombre.
     * Esencial para que los controladores puedan convertir un nombre como "ocupada" en un ID.
     * @param string $name
     * @return array|false
     */
    public function findByName(string $name): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table} WHERE nombre = :nombre";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $name);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en EstadoMesaModel::findByName: ' . $e->getMessage());
            return false;
        }
    }
}