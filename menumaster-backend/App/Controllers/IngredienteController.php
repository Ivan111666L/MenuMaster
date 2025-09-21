<?php
<<<<<<< HEAD
namespace app\Controllers;
// --- Controlador de Ingredientes ---
// Este archivo gestiona toda la lógica relacionada con los ingredientes: crear, consultar, actualizar y eliminar.
// Se conecta con los modelos y responde a las peticiones del backend.
=======
namespace App\Controllers;

>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
// Importamos los modelos que vamos a necesitar
use App\Models\IngredienteModel;
use App\Models\ProveedorModel;
use App\Models\EstadoGeneralModel;
use PDO;
use Exception;

// Clase principal que gestiona los ingredientes en el sistema.
class IngredienteController
{
    private $db;
    private $ingredienteModel;
    private $proveedorModel;
    private $estadoGeneralModel;

    // --- Constructor ---
    // Inicializa los modelos y la conexión a la base de datos.
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ingredienteModel = new IngredienteModel($this->db);
        $this->proveedorModel = new ProveedorModel($this->db); // Suponiendo que tienes un ProveedorModel
        $this->estadoGeneralModel = new EstadoGeneralModel($this->db);
    }

    /**
     * Obtiene una lista de todos los ingredientes.
     * Corresponde a: GET /api/ingredientes
     */
    // --- Listar ingredientes ---
    // Devuelve todos los ingredientes registrados en la base de datos.
    public function index(): void
    {
        $ingredientes = $this->ingredienteModel->findAll();
        if ($ingredientes === false) {
            throw new Exception("No se pudieron obtener los ingredientes.", 500);
        }
        $this->sendResponse(200, $ingredientes);
    }

    /**
     * Obtiene un único ingrediente por su ID.
     * Corresponde a: GET /api/ingredientes/{id}
     */
    // --- Consultar ingrediente por ID ---
    // Devuelve la información de un ingrediente específico.
    public function show(int $id): void
    {
        $ingrediente = $this->ingredienteModel->find($id);
        if (!$ingrediente) {
            throw new Exception("Ingrediente no encontrado.", 404);
        }
        $this->sendResponse(200, $ingrediente);
    }

    /**
     * Crea un nuevo ingrediente.
     * Corresponde a: POST /api/ingredientes
     */
    // --- Crear ingrediente ---
    // Recibe los datos de un nuevo ingrediente y lo guarda en la base de datos.
    public function store(array $data): void
    {
        $this->validarCampos($data, ['nombre', 'unidad_medida', 'stock_actual', 'stock_minimo']);

        $datosParaCrear = [
            'nombre' => $data['nombre'],
            'unidad_medida' => $data['unidad_medida'],
            'stock_actual' => $data['stock_actual'],
            'stock_minimo' => $data['stock_minimo'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio_compra' => $data['precio_compra'] ?? null,
        ];

        // Manejar relaciones: proveedor y estado
        if (!empty($data['proveedor_nombre'])) {
            $proveedor = $this->proveedorModel->findByName($data['proveedor_nombre']);
            if (!$proveedor) throw new Exception("El proveedor '{$data['proveedor_nombre']}' no existe.", 400);
            $datosParaCrear['proveedor_id'] = $proveedor['id'];
        }

        $estadoActivo = $this->estadoGeneralModel->findByName('activo');
        if (!$estadoActivo) throw new Exception("El estado 'activo' no está configurado.", 500);
        $datosParaCrear['estado_id'] = $estadoActivo['id'];
        
        $nuevoId = $this->ingredienteModel->create($datosParaCrear);
        if (!$nuevoId) {
            throw new Exception("No se pudo crear el ingrediente.", 500);
        }

        $nuevoIngrediente = $this->ingredienteModel->find($nuevoId);
        $this->sendResponse(201, $nuevoIngrediente);
    }

    /**
     * Actualiza un ingrediente existente.
     * Corresponde a: PUT /api/ingredientes/{id}
     */
    // --- Actualizar ingrediente ---
    // Modifica los datos de un ingrediente existente.
    public function update(int $id, array $data): void
    {
        if (empty($data)) {
            throw new Exception("No se proporcionaron datos para actualizar.", 400);
        }

        if (!$this->ingredienteModel->find($id)) {
            throw new Exception("Ingrediente no encontrado.", 404);
        }
        
        // El frontend puede enviar solo los campos que quiere cambiar.
        // Convertimos nombres a IDs solo si se proporcionan.
        if (isset($data['proveedor_nombre'])) {
            $proveedor = $this->proveedorModel->findByName($data['proveedor_nombre']);
            if (!$proveedor) throw new Exception("Proveedor no válido.", 400);
            $data['proveedor_id'] = $proveedor['id'];
            unset($data['proveedor_nombre']);
        }
        if (isset($data['estado_nombre'])) {
            $estado = $this->estadoGeneralModel->findByName($data['estado_nombre']);
            if (!$estado) throw new Exception("Estado no válido.", 400);
            $data['estado_id'] = $estado['id'];
            unset($data['estado_nombre']);
        }

        $exito = $this->ingredienteModel->update($id, $data);
        if (!$exito) {
            throw new Exception("No se pudo actualizar el ingrediente.", 500);
        }

        $ingredienteActualizado = $this->ingredienteModel->find($id);
        $this->sendResponse(200, $ingredienteActualizado);
    }

    /**
     * Elimina un ingrediente.
     * Corresponde a: DELETE /api/ingredientes/{id}
     */
    // --- Eliminar ingrediente ---
    // Elimina un ingrediente de la base de datos.
    public function destroy(int $id): void
    {
        if (!$this->ingredienteModel->find($id)) {
            throw new Exception("Ingrediente no encontrado.", 404);
        }
        if (!$this->ingredienteModel->delete($id)) {
            throw new Exception("No se pudo eliminar el ingrediente.", 500);
        }
        $this->sendResponse(204, []);
    }

    // --- Métodos de Ayuda ---
    // --- Validar campos requeridos ---
    // Verifica que los datos obligatorios estén presentes antes de guardar o actualizar.
    private function validarCampos(array $data, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (!isset($data[$campo]) || $data[$campo] === '') {
                throw new Exception("El campo '{$campo}' es obligatorio.", 400);
            }
        }
    }

    // --- Enviar respuesta JSON de éxito ---
    // Envía una respuesta JSON al frontend cuando la operación fue exitosa.
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            echo json_encode($data);
        }
        exit;
    }
}