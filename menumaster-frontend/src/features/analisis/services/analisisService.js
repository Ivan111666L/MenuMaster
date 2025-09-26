import api from '@/services/api';

// Obtener resumen de ventas diarias
export const getResumenVentas = async (fechaInicio, fechaFin) => {
  try {
    const response = await api.get('/cuadre_diario/resumen-ventas', {
      params: { fecha_inicio: fechaInicio, fecha_fin: fechaFin }
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener resumen de ventas:', error);
    throw error;
  }
};

// Obtener rentabilidad de productos
export const getRentabilidadProductos = async (fechaInicio, fechaFin) => {
  try {
    const response = await axios.get(`${API_URL}/cuadre_diario/rentabilidad-productos`, {
      params: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
      withCredentials: true
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener rentabilidad de productos:', error);
    throw error;
  }
};

// Obtener cuadres diarios
export const getCuadresDiarios = async (fechaInicio, fechaFin) => {
  try {
    const response = await axios.get(`${API_URL}/cuadre_diario`, {
      params: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
      withCredentials: true
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener cuadres diarios:', error);
    throw error;
  }
};

// Obtener un cuadre diario específico
export const getCuadreDiario = async (id) => {
  try {
    const response = await axios.get(`${API_URL}/cuadre_diario/${id}`, {
      withCredentials: true
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener cuadre diario:', error);
    throw error;
  }
};

// Crear o actualizar cuadre diario
export const crearOActualizarCuadreDiario = async (cuadreData) => {
  try {
    const response = await axios.post(`${API_URL}/cuadre_diario`, cuadreData, {
      withCredentials: true
    });
    return response.data;
  } catch (error) {
    console.error('Error al guardar cuadre diario:', error);
    throw error;
  }
};

// Obtener inventario con información de proveedores
export const getInventarioConProveedores = async () => {
  try {
    const response = await axios.get(`${API_URL}/cuadre_diario/inventario-proveedores`, {
      withCredentials: true
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener inventario con proveedores:', error);
    throw error;
  }
};