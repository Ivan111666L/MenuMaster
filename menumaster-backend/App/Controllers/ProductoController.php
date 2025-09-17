<?php
namespace app\Controllers;

// --- Dependencias ---
use app\Models\ProductoModel;
use app\Models\EstadoProductoModel;
use app\Models\CategoriaModel;
use app\Utils\Validator; // Se importa el Validator
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
        $this->productoModel = new ProductoModel($this->db);
        $this->categoriaModel = new CategoriaModel($this->db);
        $this->estadoProductoModel = new EstadoProductoModel($this->db);
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
     */
    public function store(array $data): void
    {
        // CORRECCIÓN: Se usa el Validator centralizado.
        Validator::validate($data, [
            'nombre' => 'required',
            'precio' => 'required',
            'categoria_nombre' => 'required',
            'estado_nombre' => 'required'
        ]);

        $categoria = $this->categoriaModel->findByName($data['categoria_nombre']);
        if (!$categoria) {
            throw new Exception("La categoría '{$data['categoria_nombre']}' no es válida.", 400);
        }
        
        $estado = $this->estadoProductoModel->findByName($data['estado_nombre']);
        if (!$estado) {
            throw new Exception("El estado '{$data['estado_nombre']}' no es válido.", 400);
        }

        $datosParaCrear = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'categoria_id' => $categoria['id'],
            'estado_id' => $estado['id']
        ];

        $nuevoId = $this->productoModel->create($datosParaCrear);
        if (!$nuevoId) {
            throw new Exception("No se pudo crear el producto.", 500);
        }

        $nuevoProducto = $this->productoModel->find($nuevoId);
        $this->sendResponse(201, $nuevoProducto);
    }

    /**
     * Actualiza un producto existente.
     * Corresponde a: PUT /api/productos/{id}
     */
    public function update(int $id, array $data): void
    {
        if (empty($data)) {
            throw new Exception("No se proporcionaron datos para actualizar.", 400);
        }
        if (!$this->productoModel->find($id)) {
            throw new Exception("Producto no encontrado.", 404);
        }
        
        if (isset($data['categoria_nombre'])) {
            $categoria = $this->categoriaModel->findByName($data['categoria_nombre']);
            if (!$categoria) throw new Exception("Categoría no válida.", 400);
            $data['categoria_id'] = $categoria['id'];
            unset($data['categoria_nombre']);
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
     */
    public function destroy(int $id): void
    {
        if (!$this->productoModel->find($id)) {
            throw new Exception("Producto no encontrado.", 404);
        }
        if (!$this->productoModel->delete($id)) {
            throw new Exception("No se pudo eliminar el producto.", 500);
        }
        $this->sendResponse(204, null);
    }

    // --- Métodos de Ayuda ---

    /**
     * Envía la respuesta HTTP en formato JSON y termina la ejecución del script.
     */
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            // CORRECCIÓN: Se estandariza la respuesta de éxito.
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }
}