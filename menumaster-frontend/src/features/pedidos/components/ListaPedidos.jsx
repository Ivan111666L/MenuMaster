import React from 'react';
import PropTypes from 'prop-types';

function ListaPedidos({ pedidos, pedidoSeleccionado, seleccionarPedido }) {
  const lista = Array.isArray(pedidos) ? pedidos : [];
  if (!Array.isArray(pedidos)) {
    console.warn('ListaPedidos: la prop "pedidos" no es un arreglo. Valor recibido:', pedidos);
  }
  return (
    <div className="facturacion-lista-pedidos">
      <h2>Pedidos para Facturar</h2>
      {lista.length === 0 ? (
        <p>No hay pedidos disponibles para facturar.</p>
      ) : (
        <ul>
          {lista.map((pedido) => (
            <li
              key={pedido.id}
              className={`facturacion-pedido-resumen ${pedidoSeleccionado?.id === pedido.id ? 'seleccionado' : ''}`}
              onClick={() => seleccionarPedido?.(pedido)}
            >
              <span>Mesa {pedido.mesa_numero}</span>
              <span>Pedido #{pedido.id}</span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

export default ListaPedidos;

ListaPedidos.propTypes = {
  pedidos: PropTypes.arrayOf(
    PropTypes.shape({
      id: PropTypes.oneOfType([PropTypes.number, PropTypes.string]).isRequired,
      mesa_numero: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    })
  ),
  pedidoSeleccionado: PropTypes.shape({
    id: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
  }),
  seleccionarPedido: PropTypes.func,
};

ListaPedidos.defaultProps = {
  pedidos: [],
  pedidoSeleccionado: null,
  seleccionarPedido: () => {},
};