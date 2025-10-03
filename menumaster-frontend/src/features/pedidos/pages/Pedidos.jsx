
import React, { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
// Import explícito del hook que carga mesas disponibles y productos
import { usePedido } from '@/features/pedidos/hooks/usePedido.js';
import PedidoForm from '@/features/pedidos/components/PedidoForm';
import PedidoResumen from '@/features/pedidos/components/PedidoResumen';
import PedidoTicket from '@/features/pedidos/components/PedidoTicket';
import pedidoService from '@/features/pedidos/services/pedidoService';
import Spinner from '@/components/Spinner';
import '@/styles/pedidos.css';



function Pedidos() {
  const location = useLocation();
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
    savePedido,
    ticketHtml,
    ticketOpen,
    setTicketOpen
  } = usePedido();

  // Eliminado flujo de lista de pedidos: impresión directa al enviar pedido

  // Preseleccionar mesa si viene en el estado de navegación
  useEffect(() => {
    const mesaId = location?.state?.mesaId;
    if (!loading && mesaId && (!pedido?.mesa_id || pedido?.mesa_id !== mesaId)) {
      handleChangeMesa(mesaId);
    }
  }, [loading, location?.state, handleChangeMesa, pedido?.mesa_id]);


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
      {/* Mostrar el ticket en un modal si está disponible */}
      <PedidoTicket html={ticketHtml} open={ticketOpen} onClose={() => setTicketOpen(false)} />
    </div>
  );
}

export default Pedidos;