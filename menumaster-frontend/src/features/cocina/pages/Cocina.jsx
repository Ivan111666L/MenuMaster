import React, { useState, useEffect } from 'react';
import cocinaService from '@/features/cocina/services/cocinaService'; // Importamos el nuevo servicio
import Spinner from '@/components/Spinner';
import Button from '@/components/Button';
import '@/styles/cocina.css'; // Importamos los estilos

const ESTADOS = {
  PENDIENTE: 'pendiente',
  EN_PREPARACION: 'en preparacion',
  LISTO_PARA_SERVIR: 'listo para servir',
};

function Cocina() {
  const [pedidos, setPedidos] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  // --- Lógica para obtener y refrescar los datos ---
  useEffect(() => {
    const fetchPedidos = async () => {
      try {
        const data = await cocinaService.getActiveOrders();
        setPedidos(data);
      } catch (err) {
        setError('No se pudieron cargar los pedidos. Intenta recargar la página.');
        console.error(err);
      } finally {
        setIsLoading(false);
      }
    };

    fetchPedidos(); // Carga inicial

    // Polling: Refrescar los pedidos cada 15 segundos
    const intervalId = setInterval(fetchPedidos, 15000);

    // Limpieza: Detener el polling cuando el componente se desmonte
    return () => clearInterval(intervalId);
  }, []); // El array vacío asegura que esto se configure solo una vez

  // --- Lógica para actualizar el estado de un pedido ---
  const avanzarEstado = async (pedido) => {
    let nuevoEstado = '';
    if (pedido.estado.toLowerCase() === ESTADOS.PENDIENTE) {
      nuevoEstado = ESTADOS.EN_PREPARACION;
    } else if (pedido.estado.toLowerCase() === ESTADOS.EN_PREPARACION) {
      nuevoEstado = ESTADOS.LISTO_PARA_SERVIR;
    } else {
      return; // No hay más estados que avanzar desde la cocina
    }

    try {
      await cocinaService.updateOrderStatus(pedido.id, nuevoEstado);
      // Actualizamos la lista localmente para una respuesta visual instantánea
      setPedidos(prevPedidos => 
        prevPedidos.map(p => p.id === pedido.id ? { ...p, estado: nuevoEstado } : p)
      );
    } catch (err) {
      alert(`Error al actualizar el pedido: ${err.response?.data?.error || err.message}`);
    }
  };

  const getBotonInfo = (estado) => {
    const estadoNormalizado = estado.toLowerCase();
    if (estadoNormalizado === ESTADOS.PENDIENTE) return { texto: 'Marcar como En Preparación', variant: 'primary' };
    if (estadoNormalizado === ESTADOS.EN_PREPARACION) return { texto: 'Marcar como Listo', variant: 'secondary' };
    return null;
  };

  if (isLoading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="cocina-container">
      <h1 className="cocina-title">Panel de Cocina</h1>
      <p className="cocina-description">Gestiona el flujo de los pedidos en tiempo real.</p>
      <div className="pedidos-list">
        {pedidos.length === 0 ? (
          <div className="no-pedidos">No hay pedidos pendientes o en preparación.</div>
        ) : (
          pedidos.map(pedido => {
            const botonInfo = getBotonInfo(pedido.estado);
            return (
              <div className={`pedido-card ${pedido.estado.toLowerCase().replace(' ', '-')}`} key={pedido.id}>
                <div className="pedido-header">
                  <span className="pedido-mesa">Mesa {pedido.mesa_numero}</span>
                  <span className="pedido-tiempo">{new Date(pedido.fecha_creacion).toLocaleTimeString()}</span>
                </div>
                <ul className="pedido-items">
                  {pedido.items && pedido.items.map((item, idx) => (
                    <li key={idx}>{item.cantidad} x {item.nombre_producto}</li>
                  ))}
                </ul>
                <div className="pedido-estado">Estado: <b>{pedido.estado}</b></div>
                {botonInfo && (
                  <Button
                    variant={botonInfo.variant}
                    onClick={() => avanzarEstado(pedido)}
                    className="w-full"
                  >
                    {botonInfo.texto}
                  </Button>
                )}
              </div>
            );
          })
        )}
      </div>
    </div>
  );
};

export default Cocina;