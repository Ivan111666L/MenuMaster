import React from 'react';
import { usePedido } from '@/features/pedidos/hooks/usePedido';
import PedidoForm from '@/features/pedidos/components/PedidoForm';
import PedidoResumen from '@/features/pedidos/components/PedidoResumen';
import Spinner from '@/components/Spinner';
import '@/styles/pedidos.css';

function Pedidos() {
  // Utilizamos el hook personalizado que creamos
  const { 
    pedido, 
    loading, 
    error, 
    mesas, 
    productos, 
    handleChangeMesa, 
    handleChangeCliente, 
    addProducto, 
    removeProducto, 
    updateCantidad, 
    savePedido 
  } = usePedido();

  if (loading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="toma-pedidos-container">
      <h1 className="toma-pedidos-title">Toma de Pedidos</h1>
      <p className="toma-pedidos-description">Selecciona la mesa y agrega productos al pedido.</p>
      <div className="pedidos-layout">
        <PedidoForm 
          pedido={pedido}
          mesas={mesas}
          productos={productos}
          handleChangeMesa={handleChangeMesa}
          handleChangeCliente={handleChangeCliente}
          addProducto={addProducto}
          seleccionarMesa={handleChangeMesa}
          agregarItem={addProducto}
          pedidoActual={pedido}
        />
        <PedidoResumen 
          pedido={pedido}
          pedidoActual={pedido}
          removeProducto={removeProducto}
          updateCantidad={updateCantidad}
          savePedido={savePedido}
          eliminarItem={removeProducto}
          limpiarPedido={() => {}}
          enviarPedido={savePedido}
        />
      </div>
    </div>
  );
}

export default Pedidos;