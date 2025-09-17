import React from 'react';

function ListaPedidos({ pedidos, pedidoSeleccionado, seleccionarPedido }) {
  return (
    <div className="facturacion-lista-pedidos">
      <h2>Pedidos Listos</h2>
      {pedidos.length === 0 ? (
        <p>No hay pedidos listos para servir.</p>
      ) : (
        <ul>
          {pedidos.map(pedido => (
            <li
              key={pedido.id}
              className={`facturacion-pedido-resumen ${pedidoSeleccionado?.id === pedido.id ? 'seleccionado' : ''}`}
              onClick={() => seleccionarPedido(pedido)}
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