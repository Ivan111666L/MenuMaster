<?php
namespace App\Controllers;

// --- Dependencias ---
use App\Models\MovimientoInventario;
use App\Models\Ingrediente;
// Importamos las clases necesarias para obtener los datos del token
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use PDO;
use Exception;

class MovimientoInventarioController
{
    private $db;
    private $movimientoModel;
    private $ingredienteModel;

    /**
     * El constructor recibe la conexión a la DB e instancia los modelos necesarios.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->movimientoModel = new MovimientoInventario($this->db);
        $this->ingredienteModel = new Ingrediente($this->db);
    }

    /**
     * Obtiene una lista de todos los movimientos de inventario.
     * Corresponde a: GET /api/inventario/movimientos
     */
    public function index(): void
    {
        $movimientos = $this->movimientoModel->findAll();
        if ($movimientos === false) {
            throw new Exception("No se pudieron obtener los movimientos de inventario.", 500);
        }
        $this->sendResponse(200, $movimientos);
    }

    /**
     * Crea un nuevo movimiento de inventario (entrada, salida o ajuste).
     * Es una operación transaccional: o se completan todos los pasos, o no se hace nada.
     * Corresponde a: POST /api/inventario/movimientos
     */
    public function store(array $data): void
    {
        // CORRECCIÓN: La autenticación ya fue manejada por el enrutador.
        // Aquí, obtenemos de forma segura el ID del usuario que realiza la acción.
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token de autorización no encontrado.", 401);
        
        $payload = AuthController::decodeTokenData($token);
        $usuarioId = $payload['id'] ?? null;
        if (!$usuarioId) throw new Exception("Token inválido: ID de usuario no encontrado.", 401);

        // --- Validación de datos de entrada ---
        $this->validarCampos($data, ['ingrediente_id', 'tipo', 'cantidad']);
        if (!in_array($data['tipo'], ['entrada', 'salida', 'ajuste'])) {
            throw new Exception("El tipo de movimiento no es válido. Debe ser 'entrada', 'salida' o 'ajuste'.", 400);
        }

        try {
            // --- INICIO DE LA TRANSACCIÓN ---
            $this->db->beginTransaction();

            // 1. Actualizar el stock del ingrediente.
            // El modelo Ingrediente debe tener la lógica para manejar stock insuficiente.
            $exitoStock = $this->ingredienteModel->actualizarStock(
                $data['ingrediente_id'],
                $data['cantidad'],
                $data ['tipo'] // Pasamos el tipo para que el modelo sepa si sumar o restar
            );
            
            if (!$exitoStock) {
                // Si el modelo devuelve false (ej. por stock insuficiente para una 'salida'), lanzamos un error.
                throw new Exception("Stock insuficiente o error al actualizar el ingrediente.", 409); // 409 Conflict
            }

            // 2. Si la actualización del stock fue exitosa, registrar el movimiento.
            $datosMovimiento = [
                'ingrediente_id' => $data['ingrediente_id'],
                'tipo_movimiento' => $data['tipo'],
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? null,
                'usuario_id' => $usuarioId // ID del usuario autenticado
            ];
            $nuevoId = $this->movimientoModel->create($datosMovimiento);
            if (!$nuevoId) {
                // Este error es grave, significa que la lógica falló después de modificar el stock.
                throw new Exception("El stock fue actualizado, pero no se pudo registrar el movimiento. La operación será revertida.");
            }

            // --- SI AMBAS OPERACIONES TIENEN ÉXITO, CONFIRMAR ---
            $this->db->commit();
            
            $nuevoMovimiento = $this->movimientoModel->find($nuevoId);
            $this->sendResponse(201, $nuevoMovimiento);

        } catch (Exception $e) {
            // --- SI CUALQUIER PASO FALLA, REVERTIR TODO ---
            $this->db->rollBack();
            // Re-lanzamos la excepción para que el enrutador centralizado la maneje
            throw new Exception("La operación falló y fue revertida: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    public function update( $request, $id) {
        try {
            $data = $request->getParsedBody();
            $this->validarCampos($data, ['ingrediente_id', 'tipo', 'cantidad']);
            $this->ingredienteModel->update($id, $data);
            $this->sendResponse(200, ["message" => "Movimiento actualizado con éxito."]);
        } catch (Exception $e) {
            $this->sendResponse($e->getCode() ?: 500, ["error" => $e->getMessage()]);
        }
    }
    public function show(int $id): void
    {
        $ingrediente = $this->ingredienteModel->find($id);
        if (!$ingrediente) {
            throw new Exception("Ingrediente no encontrado.", 404);
        }
        $this->sendResponse(200, $ingrediente);
    }
    public function destroy(int $id): void
    {
        if (!$this->ingredienteModel->find($id)) {
            throw new Exception("Ingrediente no encontrado.", 404);
        }

        if (!$this->ingredienteModel->delete($id)) {
            throw new Exception("No se pudo eliminar el ingrediente.", 500);
        }
        
        $this->sendResponse(204, []); // 204 No Content
    }


    // --- Métodos de Ayuda (Helpers) ---

    private function validarCampos(array $data, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (!isset($data[$campo]) || $data[$campo] === '') {
                throw new Exception("El campo '{$campo}' es obligatorio.", 400);
            }
        }
    }

    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            echo json_encode($data);
        }
        exit;
    }
}