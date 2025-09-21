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
<<<<<<< HEAD
=======
  }
};

export const exportarPedidoPDF = async (pedidoId) => {
  try {
    // Realizamos la petición al endpoint de exportación PDF
    const response = await api.get(`/pedidos/${pedidoId}/pdf`, {
      responseType: 'blob', // Importante: especificamos que esperamos un blob
    });
    
    // Creamos un objeto URL para el blob
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    
    // Creamos un enlace temporal y lo activamos para descargar
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `pedido-${pedidoId}.pdf`);
    document.body.appendChild(link);
    link.click();
    
    // Limpiamos
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    return true;
  } catch (error) {
    console.error('Error al exportar pedido a PDF:', error);
    throw error;
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
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

<<<<<<< HEAD
export const cambiarEstadoPedido = async (id, nuevoEstado) => {
  try {
    const response = await api.put(`/pedidos/${id}/estado`, { estado: nuevoEstado });
    return response.data;
  } catch (error) {
    console.error(`Error al cambiar estado del pedido ${id}:`, error);
    throw error;
  }
};
=======
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
