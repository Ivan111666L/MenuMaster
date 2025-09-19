import api from '@/services/api'; // Importar la instancia central de Axios

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
    console.error("Error en la solicitud de ingredientes:", error.response?.data?.error || error.message);
    throw error;
  }
}

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
 * Obtiene un ingrediente por su ID.
 * @param {number} id - ID del ingrediente
 * @returns {Promise<Object>} Datos del ingrediente
 */
const getIngredienteById = async (id) => {
  try {
    const response = await api.get(`/ingredientes/${id}`);
    return response.data.data;
  } catch (error) {
    console.error(`Error al obtener el ingrediente ${id}:`, error);
    throw error;
  }
};

/**
 * Crea un nuevo ingrediente en la base de datos.
 * @param {object} ingredienteData - Datos del nuevo ingrediente.
 * @returns {Promise<Object>} Ingrediente creado
 */
const crearIngrediente = async (ingredienteData) => {
  try {
    const response = await api.post('/api/ingredientes', ingredienteData);
    return response.data.data;
  } catch (error) {
    console.error("Error al crear ingrediente:", error);
    throw error;
  }
};

/**
 * Actualiza un ingrediente existente.
 * @param {number} id - ID del ingrediente
 * @param {object} ingredienteData - Datos actualizados
 * @returns {Promise<Object>} Ingrediente actualizado
 */
const actualizarIngrediente = async (id, ingredienteData) => {
  try {
    const response = await api.put(`/ingredientes/${id}`, ingredienteData);
    return response.data.data;
  } catch (error) {
    console.error(`Error al actualizar el ingrediente ${id}:`, error);
    throw error;
  }
};

/**
 * Elimina un ingrediente por su ID.
 * @param {number} id - El ID del ingrediente a eliminar.
 */
const eliminarIngrediente = async (id) => {
  try {
    await api.delete(`/ingredientes/${id}`);
  } catch (error) {
    console.error(`Error al eliminar el ingrediente ${id}:`, error);
    throw error;
  }
};

/**
 * Actualiza la cantidad de un ingrediente en inventario
 * @param {number} id - ID del ingrediente
 * @param {number} cantidad - Nueva cantidad
 * @returns {Promise<Object>} Ingrediente actualizado
 */
const cambiarCantidad = async (id, cantidad) => {
  try {
    const response = await api.put(`/ingredientes/${id}/cantidad`, { cantidad });
    return response.data.data;
  } catch (error) {
    console.error(`Error al cambiar cantidad del ingrediente ${id}:`, error);
    throw error;
  }
};

const ingredienteService = {
  getIngredientes,
  getIngredienteById,
  crearIngrediente,
  actualizarIngrediente,
  eliminarIngrediente,
  cambiarCantidad
};

export default ingredienteService;