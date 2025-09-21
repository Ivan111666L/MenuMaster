import React, { useEffect, useState } from 'react';
import { getFacturas } from '../services/facturaService';

function FacturasLista({ onSelect }) {
  const [facturas, setFacturas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    getFacturas()
      .then(setFacturas)
      .catch(() => setError('Error al cargar facturas'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Cargando facturas...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Historial de Facturación</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {facturas.map(factura => (
            <tr key={factura.id}>
              <td>{factura.id}</td>
              <td>{factura.cliente_nombre}</td>
              <td>{factura.fecha}</td>
              <td>${factura.total}</td>
              <td>
                <button onClick={() => onSelect(factura.id)}>Ver / Reimprimir</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default FacturasLista;
