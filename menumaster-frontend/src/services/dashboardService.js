import api from '@/services/api';
import { getProductos } from '@/services/productosService';
import { getMesas } from '@/services/mesasService';

// Obtiene métricas principales para el Dashboard de Administración
export const getDashboardMetrics = async () => {
  try {
    const res = await api.get('/dashboard');
    const payload = res?.data?.data ?? res?.data ?? {};

    // Intentamos complementar con contadores de productos y mesas
    let productosCount = null;
    let mesasCount = null;
    try {
      const productos = await getProductos();
      productosCount = Array.isArray(productos) ? productos.length : null;
    } catch {}
    try {
      const mesas = await getMesas();
      mesasCount = Array.isArray(mesas) ? mesas.length : null;
    } catch {}

    return {
      pedidos: payload.pedidosActivos ?? 0,
      ventas: payload.ventasDia ?? 0,
      productos: productosCount,
      mesas: mesasCount ?? payload.mesasTotales ?? null,
    };
  } catch (error) {
    console.warn('Fallo /dashboard, usando fuentes alternativas:', error?.message || error);
    // Fallback: construir métricas con endpoints existentes
    try {
      // Pedidos activos
      let pedidosActivos = 0;
      try {
        const pedidosRes = await api.get('/pedidos');
        const pedidosData = pedidosRes?.data?.data ?? pedidosRes?.data ?? [];
        const activos = ['pendiente', 'en preparacion', 'listo para servir'];
        pedidosActivos = Array.isArray(pedidosData)
          ? pedidosData.filter(p => activos.includes(String(p.estado || '').toLowerCase())).length
          : 0;
      } catch {}

      // Ventas del día (estadísticas)
      let ventasDia = 0;
      try {
        const statsRes = await api.get('/historial/estadisticas');
        const stats = statsRes?.data?.data ?? statsRes?.data ?? {};
        // Usar campo directo si existe
        ventasDia = Number(stats?.ventasDia ?? stats?.ventas_dia ?? 0) || 0;
      } catch {}

      // Contadores complementarios
      let productosCount = null;
      let mesasCount = null;
      try {
        const productos = await getProductos();
        productosCount = Array.isArray(productos) ? productos.length : null;
      } catch {}
      try {
        const mesas = await getMesas();
        mesasCount = Array.isArray(mesas) ? mesas.length : null;
      } catch {}

      return {
        pedidos: pedidosActivos,
        ventas: ventasDia,
        productos: productosCount,
        mesas: mesasCount,
      };
    } catch (fallbackError) {
      console.error('Error al construir métricas alternativas:', fallbackError);
      return {
        pedidos: 0,
        ventas: 0,
        productos: null,
        mesas: null,
      };
    }
  }
};

// Obtiene datos de reportes según el tipo indicado
export const getReport = async (type = 'ventas') => {
  try {
    let endpoint = null;
    switch (type) {
      case 'ventas':
        endpoint = '/historial/estadisticas';
        break;
      case 'pedidos':
        endpoint = '/historial/pedidos';
        break;
      case 'productos':
        endpoint = '/historial/productos-mas-vendidos';
        break;
      default:
        endpoint = '/historial/reporte-completo';
        break;
    }

    const res = await api.get(endpoint);
    return res?.data ?? null;
  } catch (error) {
    console.error('Error al obtener reporte de dashboard:', error);
    throw error;
  }
};

export default {
  getDashboardMetrics,
  getReport,
};