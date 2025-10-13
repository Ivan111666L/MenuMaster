import React, { useEffect, useState } from 'react';
import { getDashboardMetrics, getReport } from '@/services/dashboardService';
import { getCuadresDiarios, getRentabilidadProductos, getInventarioConProveedores } from '@/features/analisis/services/analisisService';
import Button from '@/components/Button';
import '@/styles/dashboard.css';

// Utilidad para leer horizonte global de pronóstico
const getHorizonteGlobal = () => {
  try {
    const cfgStr = localStorage.getItem('menumaster_configuraciones');
    const cfg = cfgStr ? JSON.parse(cfgStr) : null;
    const val = parseInt(cfg?.sistema?.horizonte_pronostico_default);
    return isNaN(val) ? null : val;
  } catch { return null; }
};

function DashboardAdmin() {
  const [metrics, setMetrics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [reportType, setReportType] = useState('ventas');
  const [report, setReport] = useState(null);
  // Datos reales del módulo de análisis
  const [cuadreHoy, setCuadreHoy] = useState(null);
  const [comparacionSemana, setComparacionSemana] = useState(null);
  const [topRentables, setTopRentables] = useState([]);
  const [proveedoresResumen, setProveedoresResumen] = useState([]);
  const [horizonteGlobal] = useState(getHorizonteGlobal());

  useEffect(() => {
    const cargarTodo = async () => {
      try {
        const hoy = new Date();
        const fechaFin = hoy.toISOString().slice(0, 10);
        const hace7 = new Date(hoy);
        hace7.setDate(hoy.getDate() - 7);
        const fechaInicio = hace7.toISOString().slice(0, 10);

        // Métricas principales del dashboard
        const metricsData = await getDashboardMetrics();
        setMetrics(metricsData);

        // Cuadre de hoy (ventas, costos, compras, rentabilidad)
        const cuadreResp = await getCuadresDiarios(fechaFin, fechaFin);
        if (cuadreResp?.status === 'success' && Array.isArray(cuadreResp?.data) && cuadreResp.data.length > 0) {
          const d = cuadreResp.data[0];
          const rentabilidad = parseFloat(d.total_ventas) - parseFloat(d.total_costos) - parseFloat(d.total_compras_proveedores);
          const porcentajeRentabilidad = parseFloat(d.total_ventas) > 0 ? (rentabilidad / parseFloat(d.total_ventas)) * 100 : 0;
          setCuadreHoy({
            fecha: d.fecha,
            ventas: parseFloat(d.total_ventas) || 0,
            costos: parseFloat(d.total_costos) || 0,
            compras: parseFloat(d.total_compras_proveedores) || 0,
            rentabilidad,
            porcentajeRentabilidad
          });
        }

        // Compras y costos de la semana para comparativa
        const cuadresSemanaResp = await getCuadresDiarios(fechaInicio, fechaFin);
        if (cuadresSemanaResp?.status === 'success' && Array.isArray(cuadresSemanaResp?.data)) {
          const labels = cuadresSemanaResp.data.map(item => new Date(item.fecha).toLocaleDateString());
          const ventasData = cuadresSemanaResp.data.map(item => parseFloat(item.total_ventas));
          const costosData = cuadresSemanaResp.data.map(item => parseFloat(item.total_costos));
          const comprasData = cuadresSemanaResp.data.map(item => parseFloat(item.total_compras_proveedores));
          setComparacionSemana({ labels, ventasData, costosData, comprasData });
        }

        // Top productos más rentables de la semana
        const rentResp = await getRentabilidadProductos(fechaInicio, fechaFin);
        if (rentResp?.status === 'success' && Array.isArray(rentResp?.data)) {
          const top = [...rentResp.data]
            .sort((a, b) => parseFloat(b.rentabilidad_total) - parseFloat(a.rentabilidad_total))
            .slice(0, 5);
          setTopRentables(top);
        }

        // Resumen de proveedores (comparación simple de precios actuales por ingrediente)
        const invResp = await getInventarioConProveedores();
        if (invResp?.status === 'success' && Array.isArray(invResp?.data)) {
          const porIngrediente = {};
          invResp.data.forEach(item => {
            const key = item.nombre;
            if (!porIngrediente[key]) porIngrediente[key] = [];
            porIngrediente[key].push(item);
          });
          const resumenProv = Object.entries(porIngrediente).map(([nombre, items]) => {
            const precios = items.map(i => parseFloat(i.precio_unitario || 0)).filter(n => !isNaN(n));
            const promedio = precios.length ? (precios.reduce((a, b) => a + b, 0) / precios.length) : 0;
            const mejor = items.reduce((acc, cur) => {
              const p = parseFloat(cur.precio_unitario || 0);
              if (acc == null || p < acc.precio) {
                return { proveedor: cur.proveedor_nombre || 'N/A', precio: p };
              }
              return acc;
            }, null);
            const peor = items.reduce((acc, cur) => {
              const p = parseFloat(cur.precio_unitario || 0);
              if (acc == null || p > acc.precio) {
                return { proveedor: cur.proveedor_nombre || 'N/A', precio: p };
              }
              return acc;
            }, null);
            const margen = (peor?.precio ?? 0) - (mejor?.precio ?? 0);
            return {
              ingrediente: nombre,
              mejorProveedor: mejor?.proveedor ?? 'N/A',
              mejorPrecio: mejor?.precio ?? 0,
              peorProveedor: peor?.proveedor ?? 'N/A',
              peorPrecio: peor?.precio ?? 0,
              promedioPrecio: promedio,
              margenPrecio: margen
            };
          }).slice(0, 5);
          setProveedoresResumen(resumenProv);
        }
      } catch (e) {
        console.error(e);
        setError('Error al cargar datos del dashboard');
      } finally {
        setLoading(false);
      }
    };
    cargarTodo();
  }, []);

  const handleReport = async () => {
    setReport(null);
    try {
      const data = await getReport(reportType);
      setReport(data);
    } catch {
      setReport('Error al cargar reporte');
    }
  };

  if (loading) return <div className="dashboard-app"><div className="main-content">Cargando dashboard...</div></div>;
  if (error) return <div className="dashboard-app"><div className="main-content error-banner">{error}</div></div>;

  return (
    <div className="dashboard-app">
      <div className="main-content">
        <div className="dashboard-header">
          <div>
            <h1>Dashboard de Administración</h1>
            <p className="last-updated">Resumen de métricas del sistema</p>
          </div>
          <div className="dashboard-actions">
            <Button className="refresh-button" onClick={() => window.location.reload()}>Actualizar</Button>
          </div>
        </div>

        <div className="dashboard-widgets">
          <div className="widget">
            <h3>Pedidos <span className="widget-subtitle">Activos</span></h3>
            <p className="widget-value">{metrics?.pedidos ?? '-'}</p>
          </div>
          <div className="widget">
            <h3>Ventas <span className="widget-subtitle">del día</span></h3>
            <p className="widget-value">${metrics?.ventas ?? '-'}</p>
          </div>
          <div className="widget">
            <h3>Productos <span className="widget-subtitle">registrados</span></h3>
            <p className="widget-value">{metrics?.productos ?? '-'}</p>
          </div>
          <div className="widget">
            <h3>Mesas <span className="widget-subtitle">totales</span></h3>
            <p className="widget-value">{metrics?.mesas ?? '-'}</p>
          </div>
          {cuadreHoy && (
            <div className="widget">
              <h3>Rentabilidad <span className="widget-subtitle">Hoy</span></h3>
              <p className="widget-value">${cuadreHoy.rentabilidad?.toFixed(2)}</p>
              <p className="widget-insight">Ventas: ${cuadreHoy.ventas?.toFixed(2)} · Costos: ${cuadreHoy.costos?.toFixed(2)} · Compras: ${cuadreHoy.compras?.toFixed(2)}</p>
            </div>
          )}
        </div>

        <div className="analysis-section" style={{marginTop: '1.5rem'}}>
          <div className="chart-container">
            <h3>Reportes</h3>
            {horizonteGlobal !== null && (
              <p className="text-muted" style={{marginTop: '-0.25rem'}}>Horizonte global de pronóstico: {horizonteGlobal} días</p>
            )}
            <div className="form-row" style={{display:'flex',gap:'0.5rem',alignItems:'center'}}>
              <label htmlFor="report-type" className="visually-hidden">Tipo de reporte</label>
              <select
                id="report-type"
                name="reportType"
                aria-label="Tipo de reporte"
                title="Tipo de reporte"
                value={reportType}
                onChange={e => setReportType(e.target.value)}
              >
                <option value="ventas">Ventas</option>
                <option value="pedidos">Pedidos</option>
                <option value="productos">Productos</option>
              </select>
              <Button onClick={handleReport}>Ver reporte</Button>
            </div>
            {report && (
              <div style={{marginTop:16}}>
                <h4>Reporte: {reportType}</h4>
                <pre>{typeof report === 'string' ? report : JSON.stringify(report, null, 2)}</pre>
              </div>
            )}
            {comparacionSemana && (
              <div style={{marginTop:16}}>
                <h4>Comparativa semanal</h4>
                <div className="comparacion-grid">
                  <div>
                    <strong>Ventas</strong>
                    <div className="comparacion-bars">
                      {comparacionSemana.ventasData.map((v, idx) => (
                        <div key={idx} className="bar ventas" title={`${comparacionSemana.labels[idx]}: $${v.toFixed(2)}`} style={{height: `${Math.min(100, v)}px`}} />
                      ))}
                    </div>
                  </div>
                  <div>
                    <strong>Costos</strong>
                    <div className="comparacion-bars">
                      {comparacionSemana.costosData.map((v, idx) => (
                        <div key={idx} className="bar costos" title={`${comparacionSemana.labels[idx]}: $${v.toFixed(2)}`} style={{height: `${Math.min(100, v)}px`}} />
                      ))}
                    </div>
                  </div>
                  <div>
                    <strong>Compras</strong>
                    <div className="comparacion-bars">
                      {comparacionSemana.comprasData.map((v, idx) => (
                        <div key={idx} className="bar compras" title={`${comparacionSemana.labels[idx]}: $${v.toFixed(2)}`} style={{height: `${Math.min(100, v)}px`}} />
                      ))}
                    </div>
                  </div>
                </div>
                <small className="text-muted">Escala simplificada para vista rápida. Para detalle completo, usa el módulo de Análisis.</small>
              </div>
            )}
          </div>
          <div className="top-products-container">
            <h3>Notas</h3>
            <p className="no-data">Usa el panel para generar reportes de rendimiento del día.</p>
            {topRentables && topRentables.length > 0 && (
              <div style={{marginTop:12}}>
                <h4>Top 5 productos por rentabilidad (7 días)</h4>
                <ul className="ranking-list">
                  {topRentables.map((p, idx) => (
                    <li key={idx} className="ranking-item">
                      <span className="ranking-name">{p.producto_nombre}</span>
                      <span className="ranking-stats">Rentabilidad: ${parseFloat(p.rentabilidad_total || 0).toFixed(2)}</span>
                    </li>
                  ))}
                </ul>
              </div>
            )}
            {proveedoresResumen && proveedoresResumen.length > 0 && (
              <div style={{marginTop:12}}>
                <h4>Comparación rápida de proveedores</h4>
                <div className="table-responsive">
                  <table className="table">
                    <thead>
                      <tr>
                        <th>Ingrediente</th>
                        <th>Mejor Proveedor</th>
                        <th>Mejor Precio</th>
                        <th>Peor Proveedor</th>
                        <th>Peor Precio</th>
                        <th>Promedio</th>
                        <th>Margen</th>
                      </tr>
                    </thead>
                    <tbody>
                      {proveedoresResumen.map((row, idx) => (
                        <tr key={idx}>
                          <td>{row.ingrediente}</td>
                          <td>{row.mejorProveedor}</td>
                          <td>${row.mejorPrecio.toFixed(2)}</td>
                          <td>{row.peorProveedor}</td>
                          <td>${row.peorPrecio.toFixed(2)}</td>
                          <td>${row.promedioPrecio.toFixed(2)}</td>
                          <td>${row.margenPrecio.toFixed(2)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
                <small className="text-muted">Para ver variaciones históricas y márgenes detallados, consulta Inventario y Proveedores en Análisis.</small>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default DashboardAdmin;
