import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import { getPagos } from '../services/pagoService';

function PagosLista({ pedidoId = null }) {
  const [pagos, setPagos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    setLoading(true);
    getPagos()
      .then((all) => {
        if (!Array.isArray(all)) {
          console.warn('PagosLista: respuesta inesperada del backend, se esperaba arreglo.', all);
          all = [];
        }
        if (pedidoId) {
          setPagos(all.filter(p => Number(p.pedido_id) === Number(pedidoId)));
        } else {
          setPagos(all);
        }
      })
      .catch(() => setError('Error al cargar pagos'))
      .finally(() => setLoading(false));
  }, [pedidoId]);

  if (loading) return <div>Cargando pagos...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>{pedidoId ? `Pagos del Pedido #${pedidoId}` : 'Pagos registrados'}</h2>
      <table>
        <thead>
          <tr>
            <th>Pedido</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Fecha</th>
            <th>Usuario</th>
          </tr>
        </thead>
        <tbody>
          {pagos.map(pago => (
            <tr key={pago.id}>
              <td>{pago.pedido_id || '-'}</td>
              <td>${pago.monto}</td>
              <td>{pago.metodo_nombre || pago.metodo_pago_id}</td>
              <td>{pago.fecha || pago.fecha_pago}</td>
              <td>{pago.usuario_nombre || pago.usuario_id}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default PagosLista;

PagosLista.propTypes = {
  pedidoId: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
};

PagosLista.defaultProps = {
  pedidoId: null,
};
