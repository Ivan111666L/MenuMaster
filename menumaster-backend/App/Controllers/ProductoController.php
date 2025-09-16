<?php
namespace App\Controllers;

// Importamos las clases de los modelos que vamos a necesitar
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\EstadoProducto;
use PDO;
use Exception;

class ProductoController
{
    private $db;
    private $productoModel;
    private $categoriaModel;
    private $estadoProductoModel;

    /**
     * El constructor recibe la conexión a la DB e instancia los modelos necesarios.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->productoModel = new Producto($this->db);
        $this->categoriaModel = new Categoria($this->db);
        $this->estadoProductoModel = new EstadoProducto($this->db);
    }

    /**
     * Obtiene una lista de todos los productos.
     * Corresponde a: GET /api/productos
     */
    public function index(): void
    {
        $productos = $this->productoModel->findAll();
        if ($productos === false) {
            throw new Exception("No se pudieron obtener los productos.", 500);
        }
        $this->sendResponse(200, $productos);
    }

    

    /**
     * Obtiene un único producto por su ID.
     * Corresponde a: GET /api/productos/{id}
     */
    public function show(int $id): void
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) {
            throw new Exception("Producto no encontrado.", 404);
        }
        $this->sendResponse(200, $producto);
    }

    /**
     * Crea un nuevo producto.
     * Corresponde a: POST /api/productos
     * NOTA: La autenticación y autorización (ej. verificar si es admin)
     * se maneja en el enrutador con AuthMiddleware.
     */
    public function store(array $data): void
    {
        $this->validarCampos($data, ['nombre', 'precio', 'categoria_nombre', 'estado_nombre']);

        // Buscamos los IDs correspondientes a los nombres de categoría y estado
        $categoria = $this->categoriaModel->findByName($data['categoria_nombre']);
        if (!$categoria) {
            throw new Exception("La categoría '{$data['categoria_nombre']}' no es válida.", 400);
        }
        
        $estado = $this->estadoProductoModel->findByName($data['estado_nombre']);
        if (!$estado) {
            throw new Exception("El estado '{$data['estado_nombre']}' no es válido.", 400);
        }

        // Preparamos el array de datos para el modelo, usando los IDs
        $datosParaCrear = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'categoria_id' => $categoria['id'],
            'estado_id' => $estado['id']
            // Puedes añadir otros campos opcionales aquí
        ];

        $nuevoId = $this->productoModel->create($datosParaCrear);

        if (!$nuevoId) {
            throw new Exception("No se pudo crear el producto.", 500);
        }

        $nuevoProducto = $this->productoModel->find($nuevoId);
        $this->sendResponse(201, $nuevoProducto); // 201 Created
    }

    /**
     * Actualiza un producto existente.
     * Corresponde a: PUT /api/productos/{id}
     * NOTA: La autenticación y autorización se maneja en el enrutador.
     */
    public function update(int $id, array $data): void
    {
        if (empty($data)) {
            throw new Exception("No se proporcionaron datos para actualizar.", 400);
        }

        if (!$this->productoModel->find($id)) {
            throw new Exception("Producto no encontrado.", 404);
        }
        
        // El frontend puede enviar solo los campos que quiere cambiar.
        // Convertimos nombres a IDs solo si se proporcionan en la petición.
        if (isset($data['categoria_nombre'])) {
            $categoria = $this->categoriaModel->findByName($data['categoria_nombre']);
            if (!$categoria) throw new Exception("Categoría no válida.", 400);
            $data['categoria_id'] = $categoria['id'];
            unset($data['categoria_nombre']); // Limpiamos para no confundir al modelo
        }
        if (isset($data['estado_nombre'])) {
            $estado = $this->estadoProductoModel->findByName($data['estado_nombre']);
            if (!$estado) throw new Exception("Estado no válido.", 400);
            $data['estado_id'] = $estado['id'];
            unset($data['estado_nombre']);
        }

        if (!$this->productoModel->update($id, $data)) {
            throw new Exception("No se pudo actualizar el producto.", 500);
        }

        $productoActualizado = $this->productoModel->find($id);
        $this->sendResponse(200, $productoActualizado);
    }

    /**
     * Elimina un producto.
     * Corresponde a: DELETE /api/productos/{id}
     * NOTA: La autenticación y autorización se maneja en el enrutador.
     */
    public function destroy(int $id): void
    {
        if (!$this->productoModel->find($id)) {
            throw new Exception("Producto no encontrado.", 404);
        }

        if (!$this->productoModel->delete($id)) {
            throw new Exception("No se pudo eliminar el producto.", 500);
        }

        // 204 No Content es la respuesta estándar para una eliminación exitosa
        $this->sendResponse(204, []);
    }

    // --- Métodos de Ayuda (Helpers) ---

    /**
     * Valida que los campos requeridos existan en el array de datos.
     */
    private function validarCampos(array $data, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("El campo '{$campo}' es obligatorio.", 400);
            }
        }
    }

    /**
     * Envía la respuesta HTTP en formato JSON y termina la ejecución del script.
     */
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        // Para el código 204, no se debe enviar cuerpo en la respuesta.
        if ($statusCode !== 204) {
            echo json_encode($data);
        }
        exit;
    }
}