import React, { useEffect, useState } from 'react';
import { getFactura, reimprimirFactura } from '../services/facturaService';
import Button from '@/components/Button';

function FacturaDetalle({ facturaId, onClose }) {
  const [factura, setFactura] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [reimpreso, setReimpreso] = useState(false);

  useEffect(() => {
    getFactura(facturaId)
      .then(setFactura)
      .catch(() => setError('Error al cargar factura'))
      .finally(() => setLoading(false));
  }, [facturaId]);

  const handleReimprimir = async () => {
    setReimpreso(false);
    try {
      await reimprimirFactura(facturaId);
      setReimpreso(true);
    } catch {
      setError('Error al reimprimir factura');
    }
  };

  if (loading) return <div>Cargando factura...</div>;
  if (error) return <div>{error}</div>;
  if (!factura) return null;

  return (
    <div>
      <h2>Detalle de Factura #{factura.id}</h2>
      <div>Cliente: {factura.cliente_nombre}</div>
      <div>Fecha: {factura.fecha}</div>
      <div>Total: ${factura.total}</div>
      <h3>Productos</h3>
      <ul>
        {factura.productos.map(p => (
          <li key={p.id}>{p.nombre} x {p.cantidad} - ${p.precio}</li>
        ))}
      </ul>
      <Button onClick={handleReimprimir}>Reimprimir</Button>
      {reimpreso && <div style={{color:'green'}}>Factura enviada a impresión</div>}
      <Button onClick={onClose} variant="secondary" style={{marginLeft:8}}>Cerrar</Button>
    </div>
  );
}

export default FacturaDetalle;
