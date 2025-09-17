<?php
namespace app\Models;

use PDO;
use PDOException;
use Exception;
class IngredienteModel
{
    private $conn;
    private $table_name = "ingredientes";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los ingredientes con el nombre de su proveedor y estado.
     * @return array|false
     */
    public function findAll(): array|false
    {
        $sql = "SELECT 
                    i.id, i.nombre, i.descripcion, i.unidad_medida, 
                    i.stock_actual, i.stock_minimo, i.precio_compra,
                    p.nombre AS proveedor_nombre,
                    e.nombre AS estado_nombre
                FROM 
                    {$this->table_name} i
                LEFT JOIN proveedores p ON i.proveedor_id = p.id
                LEFT JOIN estados_generales e ON i.estado_id = e.id
                ORDER BY 
                    i.nombre ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un único ingrediente por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT 
                    i.*, -- Seleccionamos todo de ingredientes para tener los IDs
                    p.nombre AS proveedor_nombre,
                    e.nombre AS estado_nombre
                FROM 
                    {$this->table_name} i
                LEFT JOIN proveedores p ON i.proveedor_id = p.id
                LEFT JOIN estados_generales e ON i.estado_id = e.id
                WHERE i.id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo ingrediente a partir de un array de datos.
     * @param array $data Datos del ingrediente.
     * @return int|false El ID del nuevo ingrediente o false si falla.
     */
    public function create(array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table_name} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => &$value) {
                $stmt->bindParam(':' . $key, $value);
            }
            $stmt->execute();
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un ingrediente a partir de un array de datos.
     * @param int $id El ID del ingrediente a actualizar.
     * @param array $data Datos a actualizar.
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fieldString = implode(', ', $fields);

        $sql = "UPDATE {$this->table_name} SET {$fieldString} WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => &$value) {
                $stmt->bindParam(':' . $key, $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
 * Actualiza el stock de un ingrediente de forma transaccional y segura.
 * Suma para 'entrada' o 'ajuste', resta para 'salida'.
 * Previene que el stock quede por debajo de cero.
 *
 * @param int $id El ID del ingrediente.
 * @param float $cantidad La cantidad a sumar o restar.
 * @param string $tipo El tipo de movimiento ('entrada', 'salida', 'ajuste').
 * @return void
 * @throws Exception Si el stock es insuficiente o si ocurre un error en la DB.
 */
public function actualizarStock(int $id, float $cantidad, string $tipo): void
{
    try {
        // 1. Iniciar una transacción para asegurar la integridad de los datos.
        $this->conn->beginTransaction();

        // 2. Obtener el ingrediente y bloquear la fila para evitar concurrencia (race conditions).
        $stmt = $this->conn->prepare("SELECT cantidad_stock FROM ingredientes WHERE id = :id FOR UPDATE");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $ingrediente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ingrediente) {
            throw new Exception("El ingrediente con ID {$id} no existe.", 404);
        }

        $stockActual = (float)$ingrediente['cantidad_stock'];
        $nuevoStock = $stockActual;

        // 3. Calcular el nuevo stock según el tipo de movimiento.
        if ($tipo === 'entrada' || $tipo === 'ajuste') {
            $nuevoStock = $stockActual + $cantidad;
        } elseif ($tipo === 'salida') {
            // 4. VALIDACIÓN CRÍTICA: Asegurarse de que hay suficiente stock.
            if ($stockActual < $cantidad) {
                throw new Exception("Stock insuficiente para el ingrediente ID {$id}. Stock actual: {$stockActual}, se requiere: {$cantidad}", 409); // 409 Conflict
            }
            $nuevoStock = $stockActual - $cantidad;
        }

        // 5. Actualizar el stock en la base de datos.
        $updateStmt = $this->conn->prepare("UPDATE ingredientes SET cantidad_stock = :nuevo_stock WHERE id = :id");
        $updateStmt->bindParam(':nuevo_stock', $nuevoStock);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();

        // 6. Si todo fue exitoso, confirmar la transacción.
        $this->conn->commit();

    } catch (Exception $e) {
        // 7. Si algo falla, revertir todos los cambios.
        $this->conn->rollBack();
        // Re-lanzar la excepción para que el controlador la maneje.
        throw $e;
    }
}

    /**
     * Elimina un ingrediente por su ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::delete: ' . $e->getMessage());
            return false;
        }
    }

    
}