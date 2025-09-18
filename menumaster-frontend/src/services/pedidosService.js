import api from './api';

export const getPedidos = async () => {
  try {
    const response = await api.get('/pedidos');
    return response.data;
  } catch (error) {
    console.error('Error al obtener pedidos:', error);
    throw error;
  }
};

export const getPedidoById = async (id) => {
  try {
    const response = await api.get(`/pedidos/${id}`);
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
    const response = await api.put(`/pedidos/${id}`, pedidoData);
    return response.data;
  } catch (error) {
    console.error(`Error al actualizar pedido ${id}:`, error);
    throw error;
  }
};

export const deletePedido = async (id) => {
  try {
    const response = await api.delete(`/pedidos/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Error al eliminar pedido ${id}:`, error);
    throw error;
  }
};

export const cambiarEstadoPedido = async (id, nuevoEstado) => {
  try {
    const response = await api.patch(`/pedidos/${id}/estado`, { estado: nuevoEstado });
    return response.data;
  } catch (error) {
    console.error(`Error al cambiar estado del pedido ${id}:`, error);
    throw error;
  }
};