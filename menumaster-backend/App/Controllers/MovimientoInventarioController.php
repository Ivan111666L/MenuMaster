<?php
namespace App\Controllers;

// --- Dependencias ---
use App\Models\MovimientoInventarioModel;
use App\Models\IngredienteModel;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Utils\Validator; // Se importa el Validator
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
        $this->movimientoModel = new MovimientoInventarioModel($this->db);
        $this->ingredienteModel = new IngredienteModel($this->db);
    }

    /**
     * Obtiene una lista de todos los movimientos de inventario.
     * Corresponde a: GET /api/movimientos-inventario
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
     * Crea un nuevo movimiento de inventario.
     * Es una operación transaccional crítica.
     * Corresponde a: POST /api/movimientos-inventario
     */
    public function store(array $data): void
    {
        // Se usa el Validator para una validación limpia.
        Validator::validate($data, [
            'ingrediente_id' => 'required',
            'tipo' => 'required',
            'cantidad' => 'required'
        ]);

        if (!in_array($data['tipo'], ['entrada', 'salida', 'ajuste'])) {
            throw new Exception("El tipo de movimiento no es válido. Debe ser 'entrada', 'salida' o 'ajuste'.", 400);
        }

        // Obtenemos de forma segura el ID del usuario que realiza la acción.
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) throw new Exception("Token de autorización no encontrado.", 401);
        
        $payload = AuthController::decodeTokenData($token);
        $usuarioId = $payload['data']['id'] ?? null;
        if (!$usuarioId) throw new Exception("Token inválido: ID de usuario no encontrado.", 401);

        try {
            // --- INICIO DE LA TRANSACCIÓN ---
            $this->db->beginTransaction();

            // 1. Actualizar el stock del ingrediente.
            $this->ingredienteModel->actualizarStock(
                $data['ingrediente_id'],
                $data['cantidad'],
                $data['tipo']
            );
            
            // 2. Registrar el movimiento.
            $datosMovimiento = [
                'ingrediente_id' => $data['ingrediente_id'],
                'tipo_movimiento' => $data['tipo'],
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? null,
                'usuario_id' => $usuarioId
            ];
            $nuevoId = $this->movimientoModel->create($datosMovimiento);
            if (!$nuevoId) {
                // Si falla el registro, la transacción se revertirá.
                throw new Exception("No se pudo registrar el movimiento.");
            }

            // --- SI TODO TIENE ÉXITO, CONFIRMAR ---
            $this->db->commit();
            
            $nuevoMovimiento = $this->movimientoModel->find($nuevoId);
            $this->sendResponse(201, $nuevoMovimiento);

        } catch (Exception $e) {
            // --- SI CUALQUIER PASO FALLA, REVERTIR TODO ---
            $this->db->rollBack();
            // Re-lanzamos la excepción para que el enrutador la maneje
           throw new Exception("La operación falló y fue revertida: " . $e->getMessage(), (int)$e->getCode() ?: 500);
        }
    }

    // Nota: Los métodos show(), update() y destroy() no suelen aplicarse a un
    // registro de "movimientos", ya que estos son inmutables (no se editan ni borran).
    // Si necesitas esa funcionalidad, puedes añadirla siguiendo el patrón de otros controladores.

    // --- Helper para enviar respuestas ---
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            // Se estandariza la respuesta de éxito
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }
}