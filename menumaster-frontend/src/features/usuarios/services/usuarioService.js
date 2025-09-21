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

/**
 * Actualiza los datos de un usuario existente
 * @param {number} id - ID del usuario
 * @param {object} userData - Datos actualizados del usuario
 * @returns {Promise<Object>} Usuario actualizado
 */
const actualizarUsuario = async (id, userData) => {
    try {
        const response = await api.put(`/usuarios/${id}`, userData);
        return response.data.data;
    } catch (error) {
        console.error('Error al actualizar usuario:', error);
        throw error;
    }
};

/**
 * Elimina un usuario por su ID
 * @param {number} id - ID del usuario a eliminar
 * @returns {Promise<void>}
 */
const eliminarUsuario = async (id) => {
    try {
        await api.delete(`/usuarios/${id}`);
    } catch (error) {
        console.error('Error al eliminar usuario:', error);
        throw error;
    }
};

const usuarioService = {
    getUsuarios,
    actualizarUsuario,
    eliminarUsuario
};

export default usuarioService;