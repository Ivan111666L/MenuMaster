<?php
namespace App\Controllers;

// CORRECCIÓN: Se usan los nombres de clase correctos para los modelos.
// Asumimos que el modelo para los estados se llama 'EstadoGeneral'.
use App\Models\MesaModel;
use App\EstadosMesa;
use PDO;
use Exception;

class MesaController
{
    private $db;
    private $mesaModel;
    // Estados de mesa centralizados via constantes

    public function __construct(PDO $db)
    {
        $this->db = $db;
        // CORRECCIÓN: Se instancian los modelos con el nombre de clase correcto.
        $this->mesaModel = new MesaModel($this->db);
        // Estados de mesa manejados por MesaModel y constantes (sin modelo genérico)
    }

    /**
     * Obtiene una lista de todas las mesas.
     * Corresponde a: GET /api/mesas
     */
    public function index(): void
    {
        $mesas = $this->mesaModel->findAll();
        if ($mesas === false) {
            throw new Exception("No se pudieron obtener las mesas.", 500);
        }
        $this->sendResponse(200, ['success' => true, 'data' => $mesas]);
    }

    /**
     * Obtiene una única mesa por su ID.
     * Corresponde a: GET /api/mesas/{id}
     */
    public function show(int $id): void
    {
        $mesa = $this->mesaModel->find($id);
        if (!$mesa) {
            throw new Exception("Mesa no encontrada.", 404);
        }
        $this->sendResponse(200, ['success' => true, 'data' => $mesa]);
    }

    /**
     * Crea una nueva mesa.
     * Corresponde a: POST /api/mesas
     */
    public function store(array $data): void
    {
        $this->validarCampos($data, ['numero', 'capacidad']);

        // Por defecto, una nueva mesa siempre está disponible (ID constante)
        $estadoDisponibleId = EstadosMesa::DISPONIBLE;

        $datosParaCrear = [
            'numero' => $data['numero'],
            'capacidad' => $data['capacidad'],
            'ubicacion' => $data['ubicacion'] ?? null,
            'estado_id' => $estadoDisponibleId
        ];
        
        $nuevoId = $this->mesaModel->create($datosParaCrear);
        if (!$nuevoId) {
            throw new Exception("No se pudo crear la mesa.", 500);
        }

        $nuevaMesa = $this->mesaModel->find($nuevoId);
        $this->sendResponse(201, ['success' => true, 'data' => $nuevaMesa]);
    }
    
    /**
     * Actualiza los datos de una mesa (nombre, capacidad, estado, etc.).
     * Corresponde a: PUT /api/mesas/{id}
     */
    public function update(int $id, array $data): void
    {
        if (empty($data)) {
            throw new Exception("No se proporcionaron datos para actualizar.", 400);
        }
        if (!$this->mesaModel->find($id)) {
            throw new Exception("Mesa no encontrada.", 404);
        }
        
        // Actualización de estado de la mesa: aceptar estado_id directo o estado_nombre
        if (isset($data['estado_id'])) {
            $estadoId = (int)$data['estado_id'];
            $cambiado = $this->mesaModel->cambiarEstadoPorId($id, $estadoId);
            if (!$cambiado) {
                throw new Exception("No se pudo cambiar el estado de la mesa.", 400);
            }
            unset($data['estado_id']);
        } elseif (isset($data['estado_nombre'])) {
            $nuevoEstado = $data['estado_nombre'];
            $cambiado = $this->mesaModel->cambiarEstado($id, $nuevoEstado);
            if (!$cambiado) {
                throw new Exception("No se pudo cambiar el estado de la mesa a '{$nuevoEstado}'.", 400);
            }
            unset($data['estado_nombre']);
        }

        // Si existen otros campos para actualizar (capacidad, ubicacion, etc.), aplicamos update
        if (!empty($data)) {
            if (!$this->mesaModel->update($id, $data)) {
                throw new Exception("No se pudo actualizar la mesa.", 500);
            }
        }

        $mesaActualizada = $this->mesaModel->find($id);
        $this->sendResponse(200, ['success' => true, 'data' => $mesaActualizada]);
    }

    /**
     * Elimina una mesa.
     * Corresponde a: DELETE /api/mesas/{id}
     */
    public function destroy(int $id): void
    {
        if (!$this->mesaModel->find($id)) {
            throw new Exception("Mesa no encontrada.", 404);
        }

        if (!$this->mesaModel->delete($id)) {
            throw new Exception("No se pudo eliminar la mesa.", 500);
        }
        
        $this->sendResponse(204, null);
    }

    /**
     * Obtiene todas las mesas disponibles.
     * Corresponde a: GET /api/mesas/disponibles
     */
    public function disponibles(): void
    {
        $mesas = $this->mesaModel->findDisponibles();
        if ($mesas === false) {
            throw new Exception("No se pudieron obtener las mesas disponibles.", 500);
        }
        $this->sendResponse(200, ['success' => true, 'data' => $mesas]);
    }

    /**
     * Resetea el estado de todas las mesas a 'disponible'.
     * Corresponde a: POST /api/mesas/reset
     */
    public function resetAll(): void
    {
        if (!$this->mesaModel->resetAll()) {
            throw new Exception("No se pudieron resetear las mesas.", 500);
        }
        $this->sendResponse(200, ['success' => true, 'message' => 'Todas las mesas han sido reseteadas a disponible.']);
    }

    /**
     * Libera una mesa específica, estableciéndola en estado 'DISPONIBLE'.
     * Corresponde a: PUT /api/mesas/{id}/liberar
     */
    public function liberar(int $id): void
    {
        if (!$this->mesaModel->find($id)) {
            throw new Exception("Mesa no encontrada.", 404);
        }

        $cambiado = $this->mesaModel->cambiarEstadoPorId($id, EstadosMesa::DISPONIBLE);
        if (!$cambiado) {
            throw new Exception("No se pudo liberar la mesa.", 400);
        }

        $mesaActualizada = $this->mesaModel->find($id);
        $this->sendResponse(200, ['success' => true, 'data' => $mesaActualizada]);
    }

    // --- Métodos de Ayuda ---
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