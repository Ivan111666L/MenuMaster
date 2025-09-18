import api from './api';

export const getMesas = async () => {
  try {
    // Obtenemos todas las mesas sin filtrar por estado
    const response = await api.get('/mesas?todas=true');
    return response.data;
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
    return response.data;
  } catch (error) {
    console.error('Error al obtener mesas disponibles:', error);
    throw error;
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