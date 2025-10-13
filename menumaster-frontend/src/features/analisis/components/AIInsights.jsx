import React, { useEffect, useState } from 'react';
import { Card, Spinner, Alert, Table } from 'react-bootstrap';
import aiService from '@/features/analisis/services/aiService';

const AIInsights = () => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [insights, setInsights] = useState([]);

  useEffect(() => {
    const cargar = async () => {
      try {
        setLoading(true);
        setError(null);
        const data = await aiService.fetchAllAnalysis();
        const out = aiService.analyzeInsights(data);
        setInsights(out);
      } catch (e) {
        console.error(e);
        setError('No se pudo generar los insights de IA');
      } finally {
        setLoading(false);
      }
    };
    cargar();
  }, []);

  if (loading) {
    return (
      <Card className="mb-3 shadow-sm analisis-card">
        <Card.Body>
          <h5 className="mb-2">Insights de IA</h5>
          <div className="d-flex align-items-center gap-2">
            <Spinner animation="border" size="sm" />
            <span>Generando análisis...</span>
          </div>
        </Card.Body>
      </Card>
    );
  }

  if (error) {
    return (
      <Card className="mb-3 shadow-sm analisis-card">
        <Card.Body>
          <h5 className="mb-2">Insights de IA</h5>
          <Alert variant="danger" className="mb-0">{error}</Alert>
        </Card.Body>
      </Card>
    );
  }

  const renderDetalle = (insight) => {
    switch (insight.tipo) {
      case 'top_platos':
        return (
          <div className="table-responsive analisis-table">
            <Table size="sm" bordered>
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Ventas</th>
                </tr>
              </thead>
              <tbody>
                {insight.detalle.map((p, i) => (
                  <tr key={i}>
                    <td>{p.nombre}</td>
                    <td>{p.cantidad}</td>
                    <td>{Number(p.ventas).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>
        );
      case 'tendencia':
        return (
          <div>
            <div>Primera mitad: {Number(insight.detalle.primeraMitad).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</div>
            <div>Segunda mitad: {Number(insight.detalle.segundaMitad).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</div>
            <div>Variación: {insight.detalle.variacion === null ? 'N/A' : insight.detalle.variacion.toFixed(2) + '%'}
            </div>
          </div>
        );
      case 'pagos_mix':
        return (
          <div className="table-responsive analisis-table">
            <Table size="sm" bordered>
              <thead>
                <tr>
                  <th>Método ID</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                {Object.entries(insight.detalle).map(([metodoId, total]) => (
                  <tr key={metodoId}>
                    <td>{metodoId}</td>
                    <td>{Number(total).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>
        );
      case 'rentabilidad':
        return (
          <div>
            <div>Ventas: {Number(insight.detalle.totalVentas).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</div>
            <div>Costos: {Number(insight.detalle.totalCostos).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</div>
            <div>Compras: {Number(insight.detalle.totalCompras).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</div>
            <div>Rentabilidad: {Number(insight.detalle.rentabilidad).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</div>
            <div>Margen: {Number(insight.detalle.margen).toFixed(2)}%</div>
          </div>
        );
      case 'horarios_pico':
        return (
          <div>
            {(insight.detalle || []).map((h, i) => (
              <div key={i}>{h.hora || h.intervalo || '—'}: {h.total_pedidos || h.total || 0} pedidos</div>
            ))}
          </div>
        );
      case 'recomendaciones':
        return (
          <ul className="mb-0">
            {insight.detalle.map((r, i) => (
              <li key={i}>{r}</li>
            ))}
          </ul>
        );
      case 'forecast':
        // Limitar filas del pronóstico usando horizonte global
        const horizonte = (() => {
          try {
            const cfgStr = localStorage.getItem('menumaster_configuraciones');
            const cfg = cfgStr ? JSON.parse(cfgStr) : null;
            const val = parseInt(cfg?.sistema?.horizonte_pronostico_default);
            return isNaN(val) ? null : val;
          } catch { return null; }
        })();
        const detalle = Array.isArray(insight.detalle)
          ? (horizonte ? insight.detalle.slice(0, horizonte) : insight.detalle)
          : [];
        return (
          <div className="table-responsive analisis-table">
            <Table size="sm" bordered>
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Ventas estimadas</th>
                </tr>
              </thead>
              <tbody>
                {detalle.map((f, i) => (
                  <tr key={i}>
                    <td>{f.fecha}</td>
                    <td>{Number(f.valor).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>
        );
      case 'outliers':
        return (
          <div className="table-responsive analisis-table">
            <Table size="sm" bordered>
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Ventas</th>
                  <th>Z-Score</th>
                </tr>
              </thead>
              <tbody>
                {insight.detalle.map((o, i) => (
                  <tr key={i}>
                    <td>{new Date(o.fecha).toLocaleDateString()}</td>
                    <td>{Number(o.valor).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 })}</td>
                    <td>{o.z.toFixed(2)}</td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>
        );
      default:
        return null;
    }
  };

  return (
    <Card className="mb-3 shadow-sm analisis-card">
      <Card.Body>
        <h5 className="mb-2">Insights de IA</h5>
        {insights.length === 0 ? (
          <div className="text-muted">No hay insights disponibles para el período.</div>
        ) : (
          insights.map((ins, idx) => (
            <div key={idx} className="mb-3">
              <strong className="d-block mb-1">{ins.titulo}</strong>
              {renderDetalle(ins)}
            </div>
          ))
        )}
      </Card.Body>
    </Card>
  );
};

export default AIInsights;