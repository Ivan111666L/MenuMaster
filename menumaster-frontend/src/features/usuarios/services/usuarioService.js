/**
 * Obtiene la lista completa de usuarios.
 */
const getUsuarios = async () => {
    const response = await api.get('/usuarios');
    return response.data.data;
};

/**
 * Actualiza los datos de un usuario.
 * @param {number} id - ID del usuario a actualizar.
 * @param {object} userData - Datos a actualizar (ej. { rol_nombre: 'nuevo_rol' }).
 */
const updateUsuario = async (id, userData) => {
    const response = await api.put(`/usuarios/${id}`, userData);
    return response.data.data;
};

/**
 * Elimina un usuario.
 * @param {number} id - ID del usuario a eliminar.
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