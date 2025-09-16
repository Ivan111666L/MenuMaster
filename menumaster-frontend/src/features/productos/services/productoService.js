import api from '@/services/api'; // Tu instancia central de Axios

// 2. Centralizar el endpoint y el manejo de errores.
const ENDPOINT = 'routes/productos_api.php';

async function handleRequest(request) {
  try {
    const response = await request();
    return response.data;
  } catch (error) {
    console.error("Error en la solicitud de productos:", error.response?.data?.error || error.message);
    throw error; // Relanzar el error original para que el componente lo maneje.
  }
}

// --- Funciones del Servicio de Productos ---

export const getProductos = () => {
  return handleRequest(() => api.get(ENDPOINT, { params: { action: 'obtenerTodos' } }));
};

export const getProductoById = (id) => {
  return handleRequest(() => api.get(ENDPOINT, { params: { action: 'obtenerPorId', id } }));
};

export const crearProducto = (productoData) => {
  // Para POST/PUT, el segundo argumento es el body, el tercero es la configuración (params).
  return handleRequest(() => api.post(ENDPOINT, productoData, { params: { action: 'crear' } }));
};

export const actualizarProducto = (id, productoData) => {
  return handleRequest(() => api.put(ENDPOINT, productoData, { params: { action: 'actualizar', id } }));
};

export const eliminarProducto = (id) => {
  return handleRequest(() => api.delete(ENDPOINT, { params: { action: 'eliminar', id } }));
};

export const cambiarCantidad = (id, cantidad) => {
  return handleRequest(() => api.put(ENDPOINT, { cantidad }, { params: { action: 'cambiarCantidad', id } }));
};


/**
 * Obtiene todas las categorías de productos desde el backend.
 */
const getCategorias = async () => {
    // Necesitaremos un nuevo endpoint en el backend para esto
    const response = await api.get('/categorias');
    return response.data.data;
};

/**
 * Crea un nuevo producto en la base de datos.
 * @param {object} productoData - Datos del nuevo producto.
 */
const createProducto = async (productoData) => {
    const response = await api.post('/productos', productoData);
    return response.data.data;
};


/**
 * Elimina un producto por su ID.
 * @param {number} id - El ID del producto a eliminar.
 */
const deleteProducto = async (id) => {
    await api.delete(`/productos/${id}`);
};

// ... (Aquí también estarían getCategorias y createProducto que creamos antes)



const productoService = {
    getCategorias,
    createProducto,
    getProductos,
    deleteProducto,
};

export default productoService;