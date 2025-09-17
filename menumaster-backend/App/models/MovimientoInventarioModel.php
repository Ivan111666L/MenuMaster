<?php
namespace app\Models;

use PDO;
use PDOException;

class MovimientoInventarioModel
{
    private $conn;
    private $table_name = "movimientos_inventario";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los movimientos de inventario, uniendo el nombre del ingrediente y del usuario.
     * @return array|false
     */
    public function findAll(): array|false
    {
        $sql = "SELECT 
                    mi.id,
                    mi.tipo_movimiento,
                    mi.cantidad,
                    mi.motivo,
                    mi.fecha_movimiento,
                    i.nombre AS ingrediente_nombre,
                    u.nombre AS usuario_nombre
                FROM 
                    {$this->table_name} mi
                LEFT JOIN ingredientes i ON mi.ingrediente_id = i.id
                LEFT JOIN usuarios u ON mi.usuario_id = u.id
                ORDER BY 
                    mi.fecha_movimiento DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MovimientoInventarioModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un único movimiento de inventario por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT 
                    mi.id,
                    mi.tipo_movimiento,
                    mi.cantidad,
                    mi.motivo,
                    mi.fecha_movimiento,
                    i.nombre AS ingrediente_nombre,
                    u.nombre AS usuario_nombre
                FROM 
                    {$this->table_name} mi
                LEFT JOIN ingredientes i ON mi.ingrediente_id = i.id
                LEFT JOIN usuarios u ON mi.usuario_id = u.id
                WHERE mi.id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MovimientoInventarioModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo movimiento de inventario a partir de un array de datos.
     * @param array $data Datos del movimiento.
     * @return int|false El ID del nuevo movimiento o false si falla.
     */
    public function create(array $data): int|false
    {
        // La columna 'fecha_movimiento' usa CURRENT_TIMESTAMP por defecto en la BD,
        // por lo que no es necesario enviarla.
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table_name} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => &$value) {
                // Se determina el tipo de parámetro para bindParam
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam(':' . $key, $value, $paramType);
            }
            
            $stmt->execute();
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en MovimientoInventarioModel::create: ' . $e->getMessage());
            return false;
        }
    }
}