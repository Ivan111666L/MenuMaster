import React, { useEffect, useState } from 'react';
import { getPagos } from '../services/pagoService';

function PagosLista() {
  const [pagos, setPagos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    getPagos()
      .then(setPagos)
      .catch(() => setError('Error al cargar pagos'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Cargando pagos...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Pagos registrados</h2>
      <table>
        <thead>
          <tr>
            <th>Monto</th>
            <th>Método</th>
            <th>Fecha</th>
            <th>Usuario</th>
          </tr>
        </thead>
        <tbody>
          {pagos.map(pago => (
            <tr key={pago.id}>
              <td>${pago.monto}</td>
              <td>{pago.metodo_nombre}</td>
              <td>{pago.fecha}</td>
              <td>{pago.usuario_nombre}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default PagosLista;
