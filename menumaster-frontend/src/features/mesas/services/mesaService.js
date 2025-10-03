import api from '@/services/api'; // Tu instancia central de Axios

/**
 * Obtiene todas las mesas desde el backend.
 */
const getMesas = async () => {
    // Llama a: GET /api/mesas
    const response = await api.get('/mesas');
    return response.data.data;
};

/**
 * Crea una nueva mesa.
 * @param {object} mesaData - Datos de la nueva mesa.
 */
const createMesa = async (mesaData) => {
    // Llama a: POST /api/mesas
    const response = await api.post('/mesas', mesaData);
    return response.data.data;
};

/**
 * Actualiza los datos de una mesa (incluyendo su estado).
 * @param {number} id - ID de la mesa a actualizar.
 * @param {object} mesaData - Datos a actualizar (ej. { estado_nombre: 'ocupada' }).
 */
const updateMesa = async (id, mesaData) => {
    // Llama a: PUT /mesas/{id}
    const response = await api.put(`/mesas/${id}`, mesaData);
    return response.data.data;
};

/**
 * Resetea una mesa individual a estado 'disponible'.
 * @param {number} id - ID de la mesa a resetear.
 */
const resetMesa = async (id) => {
    // Usamos el endpoint de actualización para establecer la mesa como disponible
    const response = await api.put(`/mesas/${id}`, { estado_nombre: 'disponible' });
    return response.data?.data ?? response.data;
};

/**
 * Elimina una mesa por su ID.
 * @param {number} id - ID de la mesa a eliminar.
 */
const deleteMesa = async (id) => {
  await api.delete(`/mesas/${id}`);
};

/**
 * Resetea el estado de todas las mesas a 'disponible'.
 * @returns {Promise<any>}
 */
const resetAllMesas = async () => {
    // Llama a: POST /api/mesas/reset
    const response = await api.post('/mesas/reset');
    return response.data;
};

const mesaService = {
    getMesas,
    createMesa,
    updateMesa,
    resetMesa,
    deleteMesa,
    resetAllMesas,
};

export default mesaService;