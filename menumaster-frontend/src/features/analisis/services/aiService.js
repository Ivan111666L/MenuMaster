import api from '@/services/api';

// Utilidad para rango de fechas por defecto (últimos 30 días)
const getDefaultRange = () => {
  const hoy = new Date();
  const inicio = new Date(hoy);
  inicio.setDate(hoy.getDate() - 30);
  const fecha_inicio = inicio.toISOString().split('T')[0];
  const fecha_fin = hoy.toISOString().split('T')[0];
  return { fecha_inicio, fecha_fin };
};

export const fetchAllAnalysis = async (params = {}) => {
  const { fecha_inicio, fecha_fin } = Object.keys(params).length ? params : getDefaultRange();
  const query = { params: { fecha_inicio, fecha_fin } };
  try {
    const [estadisticasRes, topVendidosRes, horariosPicoRes, ventasPorDiaRes, cuadresRes, comprasStatsRes, pagosRes] = await Promise.all([
      api.get('/historial/estadisticas', query),
      api.get('/historial/productos-mas-vendidos', query),
      api.get('/historial/horarios-pico', query).catch(() => ({ data: { success: false, data: [] } })),
      api.get('/historial/ventas-por-dia', query).catch(() => ({ data: { success: false, data: [] } })),
      api.get('/cuadre-diario', query),
      api.get('/compras/estadisticas', query).catch(() => ({ data: { success: false, data: {} } })),
      api.get('/pagos').catch(() => ({ data: { success: false, data: [] } })),
    ]);

    return {
      estadisticas: estadisticasRes?.data?.data ?? estadisticasRes?.data ?? {},
      topVendidos: topVendidosRes?.data?.data?.productos ?? topVendidosRes?.data?.data ?? topVendidosRes?.data ?? [],
      horariosPico: horariosPicoRes?.data?.data ?? [],
      ventasPorDia: ventasPorDiaRes?.data?.data ?? [],
      cuadres: cuadresRes?.data?.data ?? [],
      comprasStats: comprasStatsRes?.data?.data ?? {},
      pagos: pagosRes?.data?.data ?? [],
      rango: { fecha_inicio, fecha_fin }
    };
  } catch (error) {
    console.error('Error al obtener datos para IA:', error);
    throw error;
  }
};

// Pronóstico por regresión lineal simple sobre ventas diarias
const linearForecast = (ventasPorDia, steps = 7) => {
  const series = (ventasPorDia || []).map((d, i) => ({ x: i, y: parseFloat(d.total_ventas || d.total || 0), fecha: d.fecha }));
  if (series.length < 2) return [];
  const n = series.length;
  const sumX = series.reduce((a, s) => a + s.x, 0);
  const sumY = series.reduce((a, s) => a + s.y, 0);
  const sumXY = series.reduce((a, s) => a + s.x * s.y, 0);
  const sumXX = series.reduce((a, s) => a + s.x * s.x, 0);
  const denom = (n * sumXX - sumX * sumX);
  if (denom === 0) return [];
  const m = (n * sumXY - sumX * sumY) / denom;
  const b = (sumY - m * sumX) / n;
  const lastIndex = series[series.length - 1].x;
  const lastDate = new Date(series[series.length - 1].fecha);
  const out = [];
  for (let k = 1; k <= steps; k++) {
    const x = lastIndex + k;
    const y = m * x + b;
    const date = new Date(lastDate);
    date.setDate(lastDate.getDate() + k);
    out.push({ fecha: date.toISOString().split('T')[0], valor: Math.max(0, y) });
  }
  return out;
};

// Detección de outliers por z-score sobre ventas diarias
const detectOutliers = (ventasPorDia, zThreshold = 2) => {
  const vals = (ventasPorDia || []).map(d => parseFloat(d.total_ventas || d.total || 0));
  if (vals.length < 3) return [];
  const mean = vals.reduce((a, v) => a + v, 0) / vals.length;
  const variance = vals.reduce((a, v) => a + Math.pow(v - mean, 2), 0) / vals.length;
  const std = Math.sqrt(variance) || 0;
  if (std === 0) return [];
  return ventasPorDia.map((d, idx) => {
    const v = parseFloat(d.total_ventas || d.total || 0);
    const z = (v - mean) / std;
    return { fecha: d.fecha, valor: v, z };
  }).filter(p => Math.abs(p.z) >= zThreshold);
};

export const analyzeInsights = (data) => {
  const insights = [];

  // Top platos más pedidos
  const topPlatos = (data.topVendidos || []).slice(0, 5).map(p => ({
    nombre: p.producto_nombre || p.nombre,
    cantidad: p.cantidad_total || p.cantidad || 0,
    ventas: p.ventas_totales || p.total || 0,
  }));
  if (topPlatos.length) {
    insights.push({
      tipo: 'top_platos',
      titulo: 'Platos más pedidos',
      detalle: topPlatos
    });
  }

  // Tendencia de ventas (comparativa primera mitad vs segunda mitad del período)
  const ventasPorDia = data.ventasPorDia || [];
  if (ventasPorDia.length >= 2) {
    const mitad = Math.floor(ventasPorDia.length / 2);
    const suma = (arr) => arr.reduce((acc, d) => acc + parseFloat(d.total_ventas || d.total || 0), 0);
    const primeraMitad = suma(ventasPorDia.slice(0, mitad));
    const segundaMitad = suma(ventasPorDia.slice(mitad));
    const variacion = primeraMitad === 0 ? null : ((segundaMitad - primeraMitad) / primeraMitad) * 100;
    insights.push({
      tipo: 'tendencia',
      titulo: 'Tendencia de Ventas',
      detalle: {
        primeraMitad,
        segundaMitad,
        variacion
      }
    });
  }

  // Pronóstico de ventas (horizonte configurable)
  if (ventasPorDia.length >= 2) {
    const horizonte = (() => {
      try {
        const cfgStr = localStorage.getItem('menumaster_configuraciones');
        const cfg = cfgStr ? JSON.parse(cfgStr) : null;
        const val = parseInt(cfg?.sistema?.horizonte_pronostico_default);
        return isNaN(val) ? 7 : val;
      } catch { return 7; }
    })();
    const forecast = linearForecast(ventasPorDia, horizonte);
    if (forecast.length) {
      insights.push({ tipo: 'forecast', titulo: `Pronóstico de Ventas (Próximos ${horizonte} días)`, detalle: forecast });
    }
    const outliers = detectOutliers(ventasPorDia, 2.5);
    if (outliers.length) {
      insights.push({ tipo: 'outliers', titulo: 'Outliers de Ventas', detalle: outliers });
    }
  }

  // Mezcla de métodos de pago
  const pagos = data.pagos || [];
  if (pagos.length) {
    const mix = {};
    pagos.forEach(p => {
      const metodo = p.metodo_pago_id || 'desconocido';
      mix[metodo] = (mix[metodo] || 0) + parseFloat(p.monto || 0);
    });
    insights.push({ tipo: 'pagos_mix', titulo: 'Mezcla de Métodos de Pago', detalle: mix });
  }

  // Rentabilidad promedio del período según cuadres
  const cuadres = data.cuadres || [];
  if (cuadres.length) {
    const totalVentas = cuadres.reduce((acc, c) => acc + parseFloat(c.total_ventas || 0), 0);
    const totalCostos = cuadres.reduce((acc, c) => acc + parseFloat(c.total_costos || 0), 0);
    const totalCompras = cuadres.reduce((acc, c) => acc + parseFloat(c.total_compras_proveedores || 0), 0);
    const rentabilidad = totalVentas - totalCostos - totalCompras;
    const margen = totalVentas > 0 ? (rentabilidad / totalVentas) * 100 : 0;
    insights.push({ tipo: 'rentabilidad', titulo: 'Rentabilidad del período', detalle: { totalVentas, totalCostos, totalCompras, rentabilidad, margen } });
  }

  // Horarios pico
  const horarios = data.horariosPico || [];
  if (horarios.length) {
    insights.push({ tipo: 'horarios_pico', titulo: 'Horarios Pico', detalle: horarios });
  }

  // Recomendaciones básicas
  const recomendaciones = [];
  if (topPlatos.length) {
    recomendaciones.push('Incrementa stock y promoción de los platos más pedidos.');
  }
  if (cuadres.length) {
    const margen = insights.find(i => i.tipo === 'rentabilidad')?.detalle?.margen ?? 0;
    if (margen < 20) recomendaciones.push('Revisa costos y precios: margen bajo (<20%).');
  }
  if (pagos.length) {
    const metodos = insights.find(i => i.tipo === 'pagos_mix')?.detalle ?? {};
    const entradaEfectivo = metodos[1] || 0; // si 1 corresponde a efectivo normalmente
    if (entradaEfectivo > 0) recomendaciones.push('Optimiza manejo de efectivo y conciliación diaria.');
  }
  if (recomendaciones.length) {
    insights.push({ tipo: 'recomendaciones', titulo: 'Recomendaciones', detalle: recomendaciones });
  }

  return insights;
};

export default {
  fetchAllAnalysis,
  analyzeInsights,
};