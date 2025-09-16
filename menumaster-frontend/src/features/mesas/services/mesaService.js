// src/services/mesasService.js
import api from '@/services/api'; // Tu instancia central de Axios


// 2. El endpoint específico ahora se define en cada llamada.
const ENDPOINT = 'routes/mesas_api.php';

export const getMesas = async () => {
  try {
    const response = await api.get(ENDPOINT);
    return response.data;
  } catch (error) {
    console.error('Error al obtener las mesas:', error.response?.data?.error || error.message);
    // 3. Relanzamos el error original para dar más contexto al componente que lo llama.
    throw error;
  }
};

export const cambiarEstadoMesa = async (id, estado) => {
  try {
    // La acción se determina por el cuerpo del POST en el backend.
    const response = await api.post(ENDPOINT, { id, estado });
    return response.data;
  } catch (error) {
    console.error(`Error al cambiar el estado de la mesa ${id}:`, error.response?.data?.error || error.message);
    throw error;
  }
};

export const resetearMesas = async () => {
  try {
    const response = await api.post(ENDPOINT, { reset: true });
    return response.data;
  } catch (error) {
    console.error('Error al resetear las mesas:', error.response?.data?.error || error.message);
    throw error;
  }
};




/**
 * Crea una nueva mesa.
 * @param {object} mesaData - Datos de la nueva mesa.
 */
const createMesa = async (mesaData) => {
    const response = await api.post('/mesas', mesaData);
    return response.data.data;
};

/**
 * Actualiza los datos de una mesa (incluyendo su estado).
 * @param {number} id - ID de la mesa a actualizar.
 * @param {object} mesaData - Datos a actualizar.
 */
const updateMesa = async (id, mesaData) => {
    const response = await api.put(`/mesas/${id}`, mesaData);
    return response.data.data;
};

/**
 * Elimina una mesa.
 * @param {number} id - ID de la mesa a eliminar.
 */
const deleteMesa = async (id) => {
    await api.delete(`/mesas/${id}`);
};


/**
 * Resetea el estado de todas las mesas a 'disponible'.
 * Requiere un endpoint especial en el backend.
 */
const resetAllMesas = async () => {
    // POST /api/mesas/reset
    const response = await api.post('/mesas/reset');
    return response.data;
};



const mesaService = {
    getMesas,
    createMesa,
    updateMesa,
    deleteMesa,
    resetAllMesas,
};

export default mesaService;