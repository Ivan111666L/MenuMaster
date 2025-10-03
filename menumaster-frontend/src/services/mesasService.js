import api from './api';

export const getMesas = async () => {
  try {
    // Obtenemos todas las mesas sin filtrar por estado
    const response = await api.get('/mesas?todas=true');
    
    // Verificamos la estructura de la respuesta
    if (response.data && response.data.data && Array.isArray(response.data.data)) {
      return response.data.data;
    } else if (response.data && Array.isArray(response.data)) {
      return response.data;
    } else {
      console.error('Formato de respuesta incorrecto en getMesas:', response.data);
      return []; // Formato inesperado, devolvemos array vacío
    }
  } catch (error) {
    console.error('Error al obtener mesas:', error);
    return []; // Devolvemos array vacío en caso de error para evitar errores en la UI
  }
};

export const getMesaById = async (id) => {
  try {
    const response = await api.get(`/mesas/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Error al obtener mesa ${id}:`, error);
    throw error;
  }
};

export const getMesasDisponibles = async () => {
  try {
    const response = await api.get('/mesas/disponibles');
    
    // Verificamos la estructura de la respuesta
    if (response.data && response.data.data && Array.isArray(response.data.data)) {
      return response.data.data;
    } else if (response.data && Array.isArray(response.data)) {
      return response.data;
    } else {
      console.error('Formato de respuesta incorrecto en getMesasDisponibles:', response.data);
      return []; // Formato inesperado, devolvemos array vacío
    }
  } catch (error) {
    console.error('Error al obtener mesas disponibles:', error);
    return []; // Devolvemos array vacío en caso de error para evitar errores en la UI
  }
};

export const cambiarEstadoMesa = async (id, estado) => {
  try {
    const response = await api.patch(`/mesas/${id}/estado`, { estado });
    return response.data;
  } catch (error) {
    console.error(`Error al cambiar estado de la mesa ${id}:`, error);
    throw error;
  }
};

export const deleteMesa = async (id) => {
  try {
    await api.delete(`/mesas/${id}`);
  } catch (error) {
    console.error(`Error al eliminar la mesa ${id}:`, error);
    throw error;
  }
};