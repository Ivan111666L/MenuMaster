<?php
namespace app\Controllers;

use app\Models\ProveedorModel;
use PDO;
use Exception;

class ProveedorController {
    private $db;
    private $proveedorModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->proveedorModel = new ProveedorModel($db);
    }

    // Asociar ingrediente a proveedor
    public function asociarIngrediente(int $proveedor_id, int $ingrediente_id): void
    {
        try {
            $success = $this->proveedorModel->asociarIngrediente($proveedor_id, $ingrediente_id);
            if (!$success) throw new Exception('No se pudo asociar el ingrediente', 500);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Desasociar ingrediente de proveedor
    public function desasociarIngrediente(int $ingrediente_id): void
    {
        try {
            $success = $this->proveedorModel->desasociarIngrediente($ingrediente_id);
            if (!$success) throw new Exception('No se pudo desasociar el ingrediente', 500);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function index(): void
    {
        try {
            $proveedores = $this->proveedorModel->findAll();
            echo json_encode(['success' => true, 'data' => $proveedores]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        try {
            $proveedor = $this->proveedorModel->find($id);
            if (!$proveedor) throw new Exception('Proveedor no encontrado', 404);
            $ingredientes = $this->proveedorModel->getIngredientes($id);
            if (is_object($proveedor)) {
                $proveedor = (array) $proveedor;
            }
            $proveedor['ingredientes'] = $ingredientes;
            echo json_encode(['success' => true, 'data' => $proveedor]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function store(array $data): void
    {
        try {
            $id = $this->proveedorModel->create($data);
            if (!$id) throw new Exception('No se pudo crear el proveedor', 500);
            $proveedor = $this->proveedorModel->find($id);
            if (is_object($proveedor)) {
                $proveedor = (array) $proveedor;
            }
            $proveedor['ingredientes'] = $this->proveedorModel->getIngredientes($id);
            echo json_encode(['success' => true, 'data' => $proveedor]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update(int $id, array $data): void
    {
        try {
            $success = $this->proveedorModel->update($id, $data);
            if (!$success) throw new Exception('No se pudo actualizar el proveedor', 500);
            $proveedor = $this->proveedorModel->find($id);
            if (is_object($proveedor)) {
                $proveedor = (array) $proveedor;
            }
            $proveedor['ingredientes'] = $this->proveedorModel->getIngredientes($id);
            echo json_encode(['success' => true, 'data' => $proveedor]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $success = $this->proveedorModel->delete($id);
            if (!$success) throw new Exception('No se pudo eliminar el proveedor', 500);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
