import api from '@/services/api'; // Tu instancia central de Axios

// $data = json_decode(file_get_contents('php://input'), true);
/**
 * Maneja las solicitudes a la API y procesa las respuestas
 * @param {Function} request - Función que realiza la solicitud a la API
 * @returns {Promise<any>} - Datos de la respuesta
 */
async function handleRequest(request) {
  try {
    const response = await request();
    return response.data;
  } catch (error) {
    console.error("Error en la solicitud de productos:", error.response?.data?.error || error.message);
    throw error; // Relanzar el error original para que el componente lo maneje.
  }
}



/**
 * Obtiene todos los productos desde elbackend.
 * @returns {Promise<Array>} Lista de productos
 */
const getProductos = async () => {
  try {
    const response = await api.get('/api/productos');
    return response.data.data;
  } catch (error) {
    console.error("Error al obtener productos:", error);
    return [];
  }
};

/**
 * Obtiene un producto por su ID.
 * @param {number} id - ID del producto
 * @returns {Promise<Object>} Datos del producto
 */
const getProductoById = async (id) => {
  try {
    const response = await api.get(`/api/productos/${id}`);
    return response.data.data;
  } catch (error) {
    console.error(`Error al obtener el producto ${id}:`, error);
    throw error;
  }
};

/**
 * Crea un nuevo producto en la base de datos.
 * @param {object} productoData - Datos del nuevo producto.
 * @returns {Promise<Object} Producto creado
 */
const createProducto = async (productoData) => {
  try {
    const response = await api.post('/api/productos', productoData);
    return { "data": { ...response.data.data } };
  } catch (error) {
    console.error("Error al crear producto:", error.response?.data || error.message);
    throw error;
  }
};

/**
 * Actualiza un producto existente.
 * @param {number} id - ID del producto
 * @param {object} productoData - Datos actualizados
 * @returns {Promise<Object>} Producto actualizado
 */
const updateProducto = async (id, productoData) => {
  try {
    const response = await api.put(`/api/productos/${id}`, productoData);
    return response.data.data;
  } catch (error) {
    console.error(`Error al actualizar el producto ${id}:`, error);
    throw error;
  }
};

/**
 * Elimina un producto por su ID.
 * @param {number} id - El ID del producto a eliminar.
 */
const deleteProducto = async (id) => {
  try {
    await api.delete(`/api/productos/${id}`);
  } catch (error) {
    console.error(`Error al eliminar el producto ${id}:`, error);
    throw error;
  }
};

/**
 * Obtiene todas las categorías de productos desde el backend.
 * @returns {Promise<Array>} Lista de categorías
 */
const getCategorias = async () => {
  try {
    const response = await api.get('/api/categorias');
    return response.data.data;
  } catch (error) {
    console.error("Error al obtener categorías:", error);
    return [];
  }
};

/**
 * Obtiene todos los ingredientes desde el backend.
 * @returns {Promise<Array>} Lista de ingredientes
 */
const getIngredientes = async () => {
  try {
    const response = await api.get('/api/ingredientes');
    return response.data.data;
  } catch (error) {
    console.error("Error al obtener ingredientes:", error);
    return [];
  }
};

/**
 * Actualiza la cantidad de un producto en inventario
 * @param {number} id - ID del producto
 * @param {number} cantidad - Nueva cantidad
 * @returns {Promise<Object>} Producto actualizado
 */
const cambiarCantidad = async (id, cantidad) => {
  try {
    const response = await api.put(`/api/productos/${id}/cantidad`, { cantidad });
    return response.data.data;
  } catch (error) {
    console.error(`Error al cambiar cantidad del producto ${id}:`, error);
    throw error;
  }
};

const productoService = {
  getProductos,
  getProductoById,
  createProducto,
  updateProducto,
  deleteProducto,
  getCategorias,
  getIngredientes,
  cambiarCantidad
};

export default productoService;

/**
 * Ejemplo de datos para crear un producto
 */
const nuevoProducto = {
  nombre: "Producto",
  descripcion: "Descripción",
  precio: 100,
  categoria_id: 1,
  cantidad: 10
};
