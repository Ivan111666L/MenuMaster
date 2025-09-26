import api from './api';

export const getPedidos = async () => {
  try {
    const response = await api.get('/pedidos');
    
    // Verificamos la estructura de la respuesta
    if (response.data && response.data.data && Array.isArray(response.data.data)) {
      return response.data.data;
    } else if (response.data && Array.isArray(response.data)) {
      return response.data;
    } else {
      console.error('Formato de respuesta incorrecto en getPedidos:', response.data);
      return []; // Formato inesperado, devolvemos array vacío
    }
  } catch (error) {
    console.error('Error al obtener pedidos:', error);
    return []; // Devolvemos array vacío en caso de error para evitar errores en la UI
  }
};

export const getPedidoById = async (id) => {
  try {
    const response = await api.get(`/api/pedidos/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Error al obtener pedido ${id}:`, error);
    throw error;
  }
};

export const createPedido = async (pedidoData) => {
  try {
    const response = await api.post('/pedidos', pedidoData);
    return response.data;
  } catch (error) {
    console.error('Error al crear pedido:', error);
    throw error;
  }
};

export const updatePedido = async (id, pedidoData) => {
  try {
    const response = await api.put(`/api/pedidos/${id}`, pedidoData);
    return response.data;
  } catch (error) {
    console.error(`Error al actualizar pedido ${id}:`, error);
    throw error;
  }
};

export const deletePedido = async (id) => {
  try {
    const response = await api.delete(`/api/pedidos/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Error al eliminar pedido ${id}:`, error);
    throw error;
  }
};

export const cambiarEstadoPedido = async (id, nuevoEstado) => {
  try {
    const response = await api.put(`/api/pedidos/${id}/estado`, { estado: nuevoEstado });
    return response.data;
  } catch (error) {
    console.error(`Error al cambiar estado del pedido ${id}:`, error);
    throw error;
  }
};