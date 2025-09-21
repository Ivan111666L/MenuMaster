
import React, { useEffect, useState } from 'react';
import { usePedido } from '@/features/pedidos/hooks/usePedido';
import PedidoForm from '@/features/pedidos/components/PedidoForm';
import PedidoResumen from '@/features/pedidos/components/PedidoResumen';
import PedidoTicket from '@/features/pedidos/components/PedidoTicket';
import ListaPedidos from '@/features/pedidos/components/ListaPedidos';
import pedidoService from '@/features/pedidos/services/pedidoService';
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
    savePedido,
    ticketHtml,
    ticketOpen,
    setTicketOpen
  } = usePedido();

  // Estado para la lista de pedidos creados
  const [pedidosCreados, setPedidosCreados] = useState([]);
  const [pedidoSeleccionado, setPedidoSeleccionado] = useState(null);
  const [loadingPedidos, setLoadingPedidos] = useState(true);

  useEffect(() => {
    const fetchPedidos = async () => {
      setLoadingPedidos(true);
      const pedidos = await pedidoService.getPedidos();
      setPedidosCreados(pedidos);
      setLoadingPedidos(false);
    };
    fetchPedidos();
  }, []);


  if (loading || loadingPedidos) return <Spinner />;
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
      {/* Mostrar la lista de pedidos creados */}
      <div style={{marginTop: '2em'}}>
        <ListaPedidos
          pedidos={pedidosCreados}
          pedidoSeleccionado={pedidoSeleccionado}
          seleccionarPedido={setPedidoSeleccionado}
        />
      </div>
      {/* Mostrar el ticket en un modal si está disponible */}
      <PedidoTicket html={ticketHtml} open={ticketOpen} onClose={() => setTicketOpen(false)} />
    </div>
  );
}

export default Pedidos;