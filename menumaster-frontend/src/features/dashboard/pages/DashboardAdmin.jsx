import React, { useEffect, useState } from 'react';
import { getDashboardMetrics, getReport } from '@/services/dashboardService';
import Button from '@/components/Button';
import '@/styles/dashboard.css';

function DashboardAdmin() {
  const [metrics, setMetrics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [reportType, setReportType] = useState('ventas');
  const [report, setReport] = useState(null);

  useEffect(() => {
    getDashboardMetrics()
      .then(setMetrics)
      .catch(() => setError('Error al cargar métricas'))
      .finally(() => setLoading(false));
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
        </div>

        <div className="analysis-section" style={{marginTop: '1.5rem'}}>
          <div className="chart-container">
            <h3>Reportes</h3>
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
          </div>
          <div className="top-products-container">
            <h3>Notas</h3>
            <p className="no-data">Usa el panel para generar reportes de rendimiento del día.</p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default DashboardAdmin;
