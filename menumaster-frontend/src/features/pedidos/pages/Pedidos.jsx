import React from 'react';
import { usePedido } from '@/features/pedidos/hooks/usePedido';
import PedidoForm from '@/features/pedidos/components/PedidoForm';
import PedidoResumen from '@/features/pedidos/components/PedidoResumen';
import Spinner from '@/components/Spinner';
import '@/styles/pedidos.css'; // Asegúrate de tener un CSS para esta página

function Pedidos() {
  const pedidoProps = usePedido();

  if (pedidoProps.loading) return <Spinner />;
  if (pedidoProps.error) return <div className="error-message">{pedidoProps.error}</div>;

  return (
    <div className="toma-pedidos-container">
      <h1 className="toma-pedidos-title">Toma de Pedidos</h1>
      <p className="toma-pedidos-description">Selecciona la mesa y agrega productos al pedido.</p>
      <div className="pedidos-layout">
        <PedidoForm {...pedidoProps} />
        <PedidoResumen {...pedidoProps} />
      </div>
    </div>
  );
};

export default Pedidos;