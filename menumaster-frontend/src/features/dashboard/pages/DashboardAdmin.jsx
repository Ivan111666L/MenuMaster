import React, { useEffect, useState } from 'react';
import { getDashboardMetrics, getReport } from '@/services/dashboardService';
import Button from '@/components/Button';

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

  if (loading) return <div>Cargando dashboard...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h1>Dashboard de Administración</h1>
      <div style={{display:'flex',gap:32,flexWrap:'wrap'}}>
        <div>
          <h3>Pedidos</h3>
          <div>Total: {metrics?.pedidos ?? '-'}</div>
        </div>
        <div>
          <h3>Ventas</h3>
          <div>Total: ${metrics?.ventas ?? '-'}</div>
        </div>
        <div>
          <h3>Productos</h3>
          <div>Total: {metrics?.productos ?? '-'}</div>
        </div>
        <div>
          <h3>Mesas</h3>
          <div>Total: {metrics?.mesas ?? '-'}</div>
        </div>
      </div>
      <hr />
      <div>
        <h2>Reportes</h2>
        <select value={reportType} onChange={e => setReportType(e.target.value)}>
          <option value="ventas">Ventas</option>
          <option value="pedidos">Pedidos</option>
          <option value="productos">Productos</option>
        </select>
        <Button onClick={handleReport}>Ver reporte</Button>
        {report && (
          <div style={{marginTop:16}}>
            <h4>Reporte: {reportType}</h4>
            <pre>{typeof report === 'string' ? report : JSON.stringify(report, null, 2)}</pre>
          </div>
        )}
      </div>
    </div>
  );
}

export default DashboardAdmin;
