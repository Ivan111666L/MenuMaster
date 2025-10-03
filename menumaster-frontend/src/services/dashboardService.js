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
    console.error('Error al obtener métricas de dashboard:', error);
    return {
      pedidos: 0,
      ventas: 0,
      productos: null,
      mesas: null,
    };
  }
};

// Obtiene datos de reportes según el tipo indicado
export const getReport = async (type = 'ventas') => {
  try {
    let endpoint = null;
    switch (type) {
      case 'ventas':
        endpoint = '/historial/sales-stats';
        break;
      case 'pedidos':
        endpoint = '/historial/orders';
        break;
      case 'productos':
        endpoint = '/historial/top-products';
        break;
      default:
        endpoint = '/historial/complete-report';
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