import api from '@/services/api'; // Tu instancia central de Axios

/**
 * Obtiene la lista completa de usuarios desde el backend.
 */
const getUsuarios = async () => {
    const response = await api.get('/usuarios');
    return response.data.data;
};

/**
 * Actualiza los datos de un usuario por su ID.
 * @param {number} id - ID del usuario.
 * @param {object} userData - Datos a actualizar (ej. { rol: 'cocinero' }).
 */
const updateUsuario = async (id, userData) => {
    const response = await api.put(`/usuarios/${id}`, userData);
    return response.data.data;
};

/**
 * Elimina un usuario por su ID.
 * @param {number} id - ID del usuario.
 */
const deleteUsuario = async (id) => {
    await api.delete(`/usuarios/${id}`);
};

const usuarioService = {
    getUsuarios,
    updateUsuario,
    deleteUsuario,
};

export default usuarioService;