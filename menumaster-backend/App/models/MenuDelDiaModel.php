<?php
namespace app\Models;

use PDO;
use PDOException;

class MenuDelDiaModel {
    private $db;
    private $table = 'menu_del_dia';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los productos del menú para la fecha actual.
     * Se une con la tabla de productos para obtener los detalles.
     * @return array|false
     */
    public function getForToday(): array|false
    {
        $sql = "SELECT 
                    md.id, 
                    md.producto_id,
                    p.nombre as nombre_producto,
                    p.precio as precio_producto
                FROM 
                    {$this->table} md
                JOIN 
                    productos p ON md.producto_id = p.id
                WHERE 
                    md.fecha = CURDATE()";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MenuDelDiaModel::getForToday: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Añade un producto al menú para la fecha actual.
     * @param int $productoId El ID del producto a añadir.
     * @return bool
     */
    public function add(int $productoId): bool
    {
        // Primero, verificamos que el producto no esté ya en el menú de hoy para evitar duplicados.
        $checkSql = "SELECT id FROM {$this->table} WHERE producto_id = :producto_id AND fecha = CURDATE()";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetch()) {
            // El producto ya existe en el menú de hoy, no hacemos nada.
            return true;
        }

        // Si no existe, lo insertamos.
        $sql = "INSERT INTO {$this->table} (producto_id, fecha) VALUES (:producto_id, CURDATE())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MenuDelDiaModel::add: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un producto del menú para la fecha actual.
     * @param int $productoId El ID del producto a eliminar.
     * @return bool
     */
    public function remove(int $productoId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE producto_id = :producto_id AND fecha = CURDATE()";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MenuDelDiaModel::remove: ' . $e->getMessage());
            return false;
        }
    }
}