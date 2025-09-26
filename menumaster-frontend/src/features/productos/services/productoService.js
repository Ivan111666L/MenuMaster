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
    if (response.data && response.data.success) {
      return response.data.data;
    } else {
      console.error("Error al obtener productos:", response.data.error || response.statusText);
      return [];
    }
  } catch (error) {
    // Si la respuesta es HTML, muestra un error claro
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      console.error("Error: El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.");
    } else {
      console.error("Error al obtener productos:", error);
    }
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
    if (response.data && response.data.success) {
      return response.data.data;
    } else {
      throw new Error(response.data.error || 'Error al obtener el producto');
    }
  } catch (error) {
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      throw new Error('El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.');
    }
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
    if (response.data && response.data.success) {
      return { "data": { ...response.data.data } };
    } else {
      throw new Error(response.data.error || 'Error al crear producto');
    }
  } catch (error) {
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      throw new Error('El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.');
    }
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
    if (response.data && response.data.success) {
      return response.data.data;
    } else {
      throw new Error(response.data.error || 'Error al actualizar el producto');
    }
  } catch (error) {
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      throw new Error('El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.');
    }
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
    const response = await api.delete(`/api/productos/${id}`);
    if (response.data && response.data.success) {
      return true;
    } else {
      throw new Error(response.data.error || 'Error al eliminar el producto');
    }
  } catch (error) {
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      throw new Error('El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.');
    }
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
    if (response.data && response.data.success) {
      return response.data.data;
    } else {
      console.error("Error al obtener categorías:", response.data.error || response.statusText);
      return [];
    }
  } catch (error) {
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      console.error("Error: El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.");
    } else {
      console.error("Error al obtener categorías:", error);
    }
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
    if (response.data && response.data.success) {
      return response.data.data;
    } else {
      console.error("Error al obtener ingredientes:", response.data.error || response.statusText);
      return [];
    }
  } catch (error) {
    if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
      console.error("Error: El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.");
    } else {
      console.error("Error al obtener ingredientes:", error);
    }
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
