<?php
namespace App\Controllers;
// --- Controlador de Productos ---
// Este archivo gestiona toda la lógica relacionada con los productos: crear, consultar, actualizar y eliminar.
// Se conecta con los modelos y responde a las peticiones del backend.

use App\Models\ProductoModel;
use App\Models\ProductoIngredientesModel;
use App\Models\CategoriaModel;
use App\Models\EstadoProductoModel;
use App\EstadosProducto;
use App\Utils\Validator;
use PDO;
use Exception;

class ProductoController {
    private $db;
    private $productoModel;
    private $productoIngredientesModel;
    private $categoriaModel;
    private $estadoProductoModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->productoModel = new ProductoModel($this->db);
        $this->productoIngredientesModel = new ProductoIngredientesModel($this->db);
        $this->categoriaModel = new CategoriaModel($this->db);
        $this->estadoProductoModel = new EstadoProductoModel($this->db);
    }

    public function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }

    public function sendError(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    public function index(): void
    {
        try {
            $todos = isset($_GET['todos']) && $_GET['todos'] === 'true';
            $productos = $this->productoModel->findAll($todos);
            
            if ($productos === false) {
                throw new Exception("No se pudieron obtener los productos.", 500);
            }
            
            $this->sendResponse(200, $productos);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener productos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un producto específico con sus ingredientes
     * GET /api/productos/{id}
     */
    // --- Consultar producto por ID ---
    // Devuelve la información de un producto específico, incluyendo sus ingredientes.
    public function show(int $id): void
    {
        try {
            $producto = $this->productoModel->find($id);
            
            if (!$producto) {
                throw new Exception("Producto no encontrado.", 404);
            }

            // Convertir a array si es necesario
            if (is_object($producto)) {
                $producto = (array) $producto;
            }

            // Obtener ingredientes del producto
            $ingredientes = $this->productoIngredientesModel->getByProducto($id);
            $producto['ingredientes'] = $ingredientes;

            $this->sendResponse(200, $producto);
        } catch (Exception $e) {
            $this->sendError($e->getCode() ?: 500, "Error al obtener el producto: " . $e->getMessage());
        }
    }

    /**
     * Crea un nuevo producto con ingredientes
     * POST /api/productos
     */
    // --- Crear producto ---
    // Recibe los datos de un nuevo producto y lo guarda en la base de datos, junto con sus ingredientes.
    public function store(): void
    {
        try {
            // Leer datos JSON
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception("Datos inválidos.", 400);
            }

            // Validar campos requeridos
            $this->validarCamposRequeridos($data, ['nombre', 'precio', 'categoria_id']);

            // Preparar datos del producto
            $productoData = [
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? '',
                'precio' => floatval($data['precio']),
                'categoria_id' => intval($data['categoria_id']),
                'imagen_url' => $data['imagen_url'] ?? null,
                'tiempo_preparacion_min' => $data['tiempo_preparacion_min'] ?? null,
                'destacado' => isset($data['destacado']) ? (int)(bool)$data['destacado'] : 0
            ];

            // Asignar estado disponible por ID constante
            $productoData['estado_id'] = EstadosProducto::DISPONIBLE;

            // Crear el producto
            $productoCreado = $this->productoModel->create($productoData);
            
            if (!$productoCreado) {
                throw new Exception("No se pudo crear el producto.", 500);
            }

            // Obtener ID del producto creado
            $productoId = is_array($productoCreado) ? $productoCreado['id'] : $productoCreado;

            // Si hay ingredientes, asociarlos al producto
            if (!empty($data['ingredientes']) && is_array($data['ingredientes'])) {
                foreach ($data['ingredientes'] as $ingrediente) {
                    $ingredienteData = [
                        'producto_id' => $productoId,
                        'ingrediente_id' => $ingrediente['ingrediente_id'],
                        'cantidad' => $ingrediente['cantidad'] ?? 1
                    ];
                    
                    if (!$this->productoIngredientesModel->create($ingredienteData)) {
                        throw new Exception("Error al asociar ingredientes al producto.", 500);
                    }
                }
            }

            // Obtener el producto completo
            $producto = $this->productoModel->find($productoId);
            
            // Convertir a array si es necesario
            if (is_object($producto)) {
                $producto = (array) $producto;
            }
            
            $producto['ingredientes'] = $this->productoIngredientesModel->getByProducto($productoId);

            $this->sendResponse(201, $producto);
        } catch (Exception $e) {
            $this->sendError($e->getCode() ?: 500, "Error al crear producto: " . $e->getMessage());
        }
    }

    /**
     * Actualiza un producto existente
     * PUT /api/productos/{id}
     */
    // --- Actualizar producto ---
    // Modifica los datos de un producto existente y actualiza sus ingredientes si es necesario.
    public function update(int $id): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data)) {
                throw new Exception("No se proporcionaron datos para actualizar.", 400);
            }

            if (!$this->productoModel->find($id)) {
                throw new Exception("Producto no encontrado.", 404);
            }

            // Separar ingredientes de datos del producto
            $ingredientes = $data['ingredientes'] ?? null;
            unset($data['ingredientes']);

            // Actualizar producto
            $success = $this->productoModel->update($id, $data);
            if (!$success) {
                throw new Exception("No se pudo actualizar el producto.", 500);
            }

            // Si se enviaron ingredientes, actualizarlos
            if ($ingredientes !== null && is_array($ingredientes)) {
                if (!$this->productoIngredientesModel->updateProductoIngredientes($id, $ingredientes)) {
                    throw new Exception("Error al actualizar ingredientes del producto.", 500);
                }
            }

            // Obtener producto actualizado
            $producto = $this->productoModel->find($id);
            
            // Convertir a array si es necesario
            if (is_object($producto)) {
                $producto = (array) $producto;
            }
            
            $producto['ingredientes'] = $this->productoIngredientesModel->getByProducto($id);

            $this->sendResponse(200, $producto);
        } catch (Exception $e) {
            $this->sendError($e->getCode() ?: 500, "Error al actualizar producto: " . $e->getMessage());
        }
    }

    /**
     * Elimina un producto
     * DELETE /api/productos/{id}
     */
    // --- Eliminar producto ---
    // Elimina un producto de la base de datos.
    public function destroy(int $id): void
    {
        try {
            if (!$this->productoModel->find($id)) {
                throw new Exception("Producto no encontrado.", 404);
            }

            if (!$this->productoModel->delete($id)) {
                throw new Exception("No se pudo eliminar el producto.", 500);
            }

            $this->sendResponse(204, []);
        } catch (Exception $e) {
            $this->sendError($e->getCode() ?: 500, "Error al eliminar producto: " . $e->getMessage());
        }
    }

    /**
     * Obtiene productos agrupados por categoría (optimizado para menús)
     * GET /api/productos/by-category
     */
    // --- Listar productos por categoría ---
    // Devuelve los productos agrupados por categoría para mostrar en menús.
    public function byCategory(): void
    {
        try {
            $productos = $this->productoModel->findByCategory();
            
            if ($productos === false) {
                throw new Exception("No se pudieron obtener los productos por categoría.", 500);
            }
            
            $this->sendResponse(200, $productos);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener productos por categoría: " . $e->getMessage());
        }
    }

    /**
     * Busca productos por nombre
     * GET /api/productos/search?q={termino}&limit={limite}
     */
    // --- Buscar productos ---
    // Permite buscar productos por nombre y limitar la cantidad de resultados.
    public function searchProducts(): void
    {
        try {
            $query = $_GET['q'] ?? '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            if (empty($query)) {
                throw new Exception("Se requiere un término de búsqueda.", 400);
            }
            
            $productos = $this->productoModel->search($query, $limit);
            
            if ($productos === false) {
                throw new Exception("Error en la búsqueda de productos.", 500);
            }
            
            $this->sendResponse(200, $productos);
        } catch (Exception $e) {
            $this->sendError(500, "Error en la búsqueda: " . $e->getMessage());
        }
    }

    /**
     * Obtiene productos destacados
     * GET /api/productos/featured?limit={limite}
     */
    // --- Productos destacados ---
    // Devuelve los productos marcados como destacados para mostrar en la portada o menú principal.
    public function featured(): void
    {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
            $productos = $this->productoModel->findFeatured($limit);
            
            if ($productos === false) {
                throw new Exception("No se pudieron obtener los productos destacados.", 500);
            }
            
            $this->sendResponse(200, $productos);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener productos destacados: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el estado de un producto
     * PATCH /api/productos/{id}/status
     */
    // --- Actualizar estado de producto ---
    // Permite cambiar el estado (disponible/no disponible) de un producto.
    public function updateProductStatus(int $id, array $data): void
    {
        try {
            // Permitir enviar 'estado_id' directamente; mantener compatibilidad con 'estado' por nombre
            if (isset($data['estado_id'])) {
                if (!$this->productoModel->updateStatusPorId($id, (int)$data['estado_id'])) {
                    throw new Exception("No se pudo actualizar el estado del producto.", 500);
                }
            } else {
                Validator::validate($data, ['estado' => 'required']);
                if (!$this->productoModel->updateStatus($id, $data['estado'])) {
                    throw new Exception("No se pudo actualizar el estado del producto.", 500);
                }
            }
            
            $this->sendResponse(200, ["mensaje" => "Estado del producto actualizado correctamente."]);
        } catch (Exception $e) {
            $this->sendError(500, "Error al actualizar estado: " . $e->getMessage());
        }
    }

    /**
     * Valida campos requeridos
     */
    // --- Validar campos requeridos ---
    // Verifica que los datos obligatorios estén presentes antes de guardar o actualizar.
    private function validarCamposRequeridos(array $data, array $campos): void
    {
        foreach ($campos as $campo) {
            if (!isset($data[$campo]) || $data[$campo] === '') {
                throw new Exception("El campo '{$campo}' es obligatorio.", 400);
            }
        }
    }

    /**
     * Envía respuesta JSON de éxito
     */
    // --- Enviar respuesta JSON de éxito ---
    // Envía una respuesta JSON al frontend cuando la operación fue exitosa.
   



}
    

    

