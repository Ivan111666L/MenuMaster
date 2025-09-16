// src/components/ListaPedidos.js
import React from 'react';
import PropTypes from 'prop-types';
import "@/styles/global.css";
const ListaPedidos = ({ pedidos, seleccionarPedido, pedidoSeleccionado }) => {
    return (
        <div className="lista-pedidos">
            <h2>Pedidos Completados</h2>
            <ul>
                {pedidos.map(pedido => (
                    <li 
                        key={pedido.id} 
                        onClick={() => seleccionarPedido(pedido.id)}
                        className={pedidoSeleccionado?.id === pedido.id ? 'seleccionado' : ''}
                    >
                        <span>Pedido: {pedido.id}</span>
                        <span>Mesa: {pedido.mesa}</span>
                        <strong>${pedido.total.toFixed(2)}</strong>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default ListaPedidos;