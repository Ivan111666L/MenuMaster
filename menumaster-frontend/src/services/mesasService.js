import api from './api';
import { ESTADOS_MESA } from '@/utils/constant';

const PUBLIC_BASE_URL = 'http://localhost/MenuMaster/menumaster-backend/public';

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
    // Intentamos primero vía API autenticada
    const response = await api.get('/mesas/disponibles');
    const isDisponible = (m) => {
      const estado = (m?.estado ?? m?.estado_nombre ?? '').toLowerCase();
      return estado === ESTADOS_MESA.DISPONIBLE;
    };
    const filtrarDisponibles = (arr) => Array.isArray(arr) ? arr.filter(isDisponible) : [];

    if (response.data && response.data.data && Array.isArray(response.data.data)) {
      return filtrarDisponibles(response.data.data);
    } else if (response.data && Array.isArray(response.data)) {
      return filtrarDisponibles(response.data);
    }
    
    // Si el formato es inesperado o vacío, intentamos fallback público
    console.warn('Formato inesperado/ vacío desde /mesas/disponibles, intentando fallback público');
    const pubRes = await fetch(`${PUBLIC_BASE_URL}/simple_mesas.php`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    if (!pubRes.ok) {
      throw new Error(`HTTP error! status: ${pubRes.status}`);
    }
    const pubData = await pubRes.json();
    if (pubData.success && Array.isArray(pubData.data)) {
      return filtrarDisponibles(pubData.data);
    }
    console.error('Formato de respuesta incorrecto en fallback público getMesasDisponibles:', pubData);
    return [];
  } catch (error) {
    console.warn('Fallo API autenticada, intentando fallback público para mesas:', error?.message || error);
    try {
      const pubRes = await fetch(`${PUBLIC_BASE_URL}/simple_mesas.php`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      });
      if (!pubRes.ok) {
        throw new Error(`HTTP error! status: ${pubRes.status}`);
      }
      const pubData = await pubRes.json();
      if (pubData.success && Array.isArray(pubData.data)) {
        return filtrarDisponibles(pubData.data);
      }
      console.error('Formato de respuesta incorrecto en fallback público getMesasDisponibles:', pubData);
      return [];
    } catch (pubErr) {
      console.error('Error al obtener mesas disponibles (fallback público):', pubErr);
      return []; // Devolvemos array vacío en caso de error para evitar errores en la UI
    }
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