import api from '@/services/api'; // Importamos nuestra instancia de Axios

/**
 * Obtiene la lista completa de todos los productos disponibles.
 */
const getAllProducts = async () => {
  const response = await api.get('/productos');
  return response.data.data;
};

/**
 * Obtiene los productos que están actualmente en el menú del día.
 */
const getMenuDelDia = async () => {
  const response = await api.get('/menu-del-dia');
  return response.data.data;
};

/**
 * Agrega un producto al menú del día.
 * @param {number} productoId - El ID del producto a agregar.
 */
const addProductToMenu = async (productoId) => {
  const response = await api.post('/menu-del-dia', { producto_id: productoId });
  return response.data;
};

/**
 * Elimina un producto del menú del día.
 * @param {number} productoId - El ID del producto a eliminar.
 */
const removeProductFromMenu = async (productoId) => {
  const response = await api.delete(`/api/menu-del-dia/${productoId}`);
  return response.data;
};

const menuDelDiaService = {
  getAllProducts,
  getMenuDelDia,
  addProductToMenu,
  removeProductFromMenu,
};

export default menuDelDiaService;