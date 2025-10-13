import React, { useEffect, useState } from 'react';
import { Card } from 'react-bootstrap';
import { getPagos } from '@/features/pagos/services/pagoService';

const PaymentSummary = () => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [totalHoy, setTotalHoy] = useState(0);
  const [cantidadHoy, setCantidadHoy] = useState(0);

  useEffect(() => {
    const hoy = new Date();
    const hoyStr = hoy.toISOString().slice(0, 10); // YYYY-MM-DD
    setLoading(true);
    getPagos()
      .then((pagos) => {
        const pagosHoy = pagos.filter(p => {
          const fecha = (p.fecha_pago || p.fecha || '').toString();
          return fecha.startsWith(hoyStr);
        });
        const total = pagosHoy.reduce((acc, p) => acc + Number(p.monto || 0), 0);
        setCantidadHoy(pagosHoy.length);
        setTotalHoy(total);
      })
      .catch(() => setError('No se pudo cargar el resumen de pagos'))
      .finally(() => setLoading(false));
  }, []);

  return (
    <Card className="mb-3 shadow-sm">
      <Card.Body>
        <Card.Title className="mb-2">Resumen de Pagos (Hoy)</Card.Title>
        {loading ? (
          <div>Cargando...</div>
        ) : error ? (
          <div style={{ color: 'red' }}>{error}</div>
        ) : (
          <div style={{ display: 'flex', gap: '2rem', alignItems: 'center' }}>
            <div>
              <div style={{ fontSize: '1.8rem', fontWeight: 700 }}>{cantidadHoy}</div>
              <div style={{ color: '#6B7280' }}>Pagos registrados</div>
            </div>
            <div>
              <div style={{ fontSize: '1.8rem', fontWeight: 700 }}>${totalHoy.toFixed(2)}</div>
              <div style={{ color: '#6B7280' }}>Total cobrado</div>
            </div>
          </div>
        )}
      </Card.Body>
    </Card>
  );
};

export default PaymentSummary;