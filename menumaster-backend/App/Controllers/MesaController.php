<?php
namespace App\Controllers;

// CORRECCIÓN: Se usan los nombres de clase correctos para los modelos.
// Asumimos que el modelo para los estados se llama 'EstadoGeneral'.
use App\Models\MesaModel;
use App\Models\EstadoGeneralModel; // Se usa un modelo de estado genérico
use PDO;
use Exception;

class MesaController
{
    private $db;
    private $mesaModel;
    private $estadoGeneralModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        // CORRECCIÓN: Se instancian los modelos con el nombre de clase correcto.
        $this->mesaModel = new MesaModel($this->db);
        $this->estadoGeneralModel = new EstadoGeneralModel($this->db);
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

        // Por defecto, una nueva mesa siempre está 'disponible'
        $estadoDisponible = $this->estadoGeneralModel->findByName('disponible');
        if (!$estadoDisponible) {
            throw new Exception("El estado 'disponible' no está configurado en la base de datos.", 500);
        }

        $datosParaCrear = [
            'numero' => $data['numero'],
            'capacidad' => $data['capacidad'],
            'ubicacion' => $data['ubicacion'] ?? null,
            'estado_id' => $estadoDisponible['id']
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
        
        // Si se envía el nombre de un estado, lo convertimos a su ID
        if (isset($data['estado_nombre'])) {
            $estado = $this->estadoGeneralModel->findByName($data['estado_nombre']);
            if (!$estado) {
                throw new Exception("El estado '{$data['estado_nombre']}' no es válido.", 400);
            }
            $data['estado_id'] = $estado['id'];
            unset($data['estado_nombre']); // Limpiamos para no confundir al modelo
        }
        
        if (!$this->mesaModel->update($id, $data)) {
            throw new Exception("No se pudo actualizar la mesa.", 500);
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
     * Resetea el estado de todas las mesas a 'disponible'.
     * Corresponde a: POST /api/mesas/reset
     */
    public function resetAll(): void
    {
        if (!$this->mesaModel->resetAll()) {
            throw new Exception("No se pudieron resetear las mesas.", 500);
        }
        
        $this->sendResponse(200, ['success' => true, 'message' => 'Todas las mesas han sido reseteadas a "disponible".']);
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