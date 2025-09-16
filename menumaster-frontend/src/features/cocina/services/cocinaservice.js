import api from '@/services/api'; // Importamos nuestra instancia de Axios

/**
 * Obtiene todos los pedidos que están en estados relevantes para la cocina
 * (ej. 'pendiente', 'en preparacion').
 */
const getActiveOrders = async () => {
  // Hacemos una petición GET a /api/pedidos, filtrando por estado.
  // El backend debe poder manejar múltiples estados en el query param.
  const response = await api.get('/pedidos?estado=pendiente,en preparacion');
  return response.data.data;
};

/**
 * Actualiza el estado de un pedido específico.
 * @param {number} pedidoId - El ID del pedido a actualizar.
 * @param {string} nuevoEstado - El nuevo estado del pedido.
 */
const updateOrderStatus = async (pedidoId, nuevoEstado) => {
  // Hacemos una petición PUT a la ruta específica para actualizar el estado.
  const response = await api.put(`/pedidos/${pedidoId}/estado`, { estado: nuevoEstado });
  return response.data;
};

const cocinaService = {
  getActiveOrders,
  updateOrderStatus,
};

export default cocinaService;