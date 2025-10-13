import api from '@/services/api';

// Obtener resumen de ventas diarias
export const getResumenVentas = async (fechaInicio, fechaFin) => {
  try {
    const response = await api.get('/cuadre-diario/resumen-ventas', {
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
    const response = await api.get('/cuadre-diario/rentabilidad-productos', {
      params: { fecha_inicio: fechaInicio, fecha_fin: fechaFin }
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
    const response = await api.get('/cuadre-diario', {
      params: { fecha_inicio: fechaInicio, fecha_fin: fechaFin }
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
    const response = await api.get(`/cuadre-diario/${id}`);
    return response.data;
  } catch (error) {
    console.error('Error al obtener cuadre diario:', error);
    throw error;
  }
};

// Crear o actualizar cuadre diario
export const crearOActualizarCuadreDiario = async (cuadreData) => {
  try {
    const response = await api.post('/cuadre-diario', cuadreData);
    return response.data;
  } catch (error) {
    console.error('Error al guardar cuadre diario:', error);
    throw error;
  }
};

// Obtener inventario con información de proveedores
export const getInventarioConProveedores = async () => {
  try {
    const response = await api.get('/cuadre-diario/inventario-proveedores');
    return response.data;
  } catch (error) {
    console.error('Error al obtener inventario con proveedores:', error);
    throw error;
  }
};

// Obtener productos más vendidos en un período
export const getProductosMasVendidos = async (fechaInicio, fechaFin) => {
  try {
    const response = await api.get('/historial/productos-mas-vendidos', {
      params: { fecha_inicio: fechaInicio, fecha_fin: fechaFin }
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener productos más vendidos:', error);
    throw error;
  }
};

// Listado de pagos (para análisis). Devuelve { success, data }
export const getPagosListado = async () => {
  try {
    const response = await api.get('/pagos');
    return response.data;
  } catch (error) {
    console.error('Error al obtener pagos:', error);
    throw error;
  }
};

// Listado de métodos de pago. Devuelve { success, data }
export const getMetodosPagoListado = async () => {
  try {
    const response = await api.get('/pagos/metodos');
    return response.data;
  } catch (error) {
    console.error('Error al obtener métodos de pago:', error);
    throw error;
  }
};

// Listado de usuarios/meseros para filtros
export const getUsuariosListado = async () => {
  try {
    const response = await api.get('/usuarios');
    return response.data;
  } catch (error) {
    console.error('Error al obtener usuarios:', error);
    throw error;
  }
};

// Ventas por día filtradas por usuario/mesero
export const getVentasPorDiaPorUsuario = async (fechaInicio, fechaFin, usuarioIds) => {
  try {
    const response = await api.get('/historial/ventas-por-dia-usuario', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        usuario_id: Array.isArray(usuarioIds) ? usuarioIds.join(',') : usuarioIds
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error al obtener ventas por día por usuario:', error);
    throw error;
  }
};