import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Spinner from '@/components/Spinner';
import Button from '@/components/Button';
import PedidoForm from '@/features/pedidos/components/PedidoForm';
import PedidoResumen from '@/features/pedidos/components/PedidoResumen';
import { getPedidoById, updatePedido } from '@/services/pedidosService';
import { getMesas } from '@/services/mesasService';
import { getProductos } from '@/services/productosService';
import '@/styles/pedidos.css';

function PedidoEditar() {
  const { pedidoId } = useParams();
  const navigate = useNavigate();

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [mesas, setMesas] = useState([]);
  const [productos, setProductos] = useState([]);
  const [pedido, setPedido] = useState({ mesa_id: '', items: [], notas: '', cliente: '' });

  const cargarData = useCallback(async () => {
    try {
      setLoading(true);
      const [mesasData, productosData] = await Promise.all([
        getMesas(),
        getProductos(),
      ]);
      setMesas(Array.isArray(mesasData) ? mesasData : []);
      setProductos(Array.isArray(productosData) ? productosData : []);

      if (pedidoId) {
        const pedidoData = await getPedidoById(pedidoId);
        // Normalizar estructura esperada por componentes
        const detalles = pedidoData?.detalles || [];
        const items = detalles.map(d => ({
          producto_id: d.producto_id,
          nombre: d.producto_nombre,
          precio: Number(d.precio_unitario || d.precio || 0),
          cantidad: Number(d.cantidad || 1),
        }));
        setPedido({
          mesa_id: pedidoData?.mesa_id || '',
          cliente: pedidoData?.cliente || '',
          items,
          notas: '',
        });
      }
      setLoading(false);
    } catch (err) {
      console.error('Error al cargar datos de edición:', err);
      setError('No se pudieron cargar los datos del pedido.');
      setLoading(false);
    }
  }, [pedidoId]);

  useEffect(() => {
    cargarData();
  }, [cargarData]);

  const seleccionarMesa = (mesaId) => {
    setPedido(prev => ({ ...prev, mesa_id: mesaId }));
  };

  const agregarItem = (producto) => {
    setPedido(prev => {
      const items = [...prev.items];
      const idx = items.findIndex(it => it.producto_id === producto.id);
      if (idx >= 0) {
        items[idx] = { ...items[idx], cantidad: items[idx].cantidad + 1 };
      } else {
        items.push({ producto_id: producto.id, nombre: producto.nombre, precio: producto.precio, cantidad: 1 });
      }
      return { ...prev, items };
    });
  };

  const removeProducto = (productoId) => {
    setPedido(prev => ({ ...prev, items: prev.items.filter(it => it.producto_id !== productoId) }));
  };

  const updateCantidad = (productoId, cantidad) => {
    if (cantidad <= 0) return removeProducto(productoId);
    setPedido(prev => ({
      ...prev,
      items: prev.items.map(it => it.producto_id === productoId ? { ...it, cantidad } : it)
    }));
  };

  const limpiarPedido = () => {
    setPedido(prev => ({ ...prev, items: [] }));
  };

  const saveEdicion = async () => {
    try {
      if (!pedidoId) {
        setError('Ruta inválida: falta ID de pedido.');
        return;
      }
      if (!pedido.mesa_id) {
        setError('Selecciona una mesa.');
        return;
      }
      if (pedido.items.length === 0) {
        setError('Agrega al menos un producto.');
        return;
      }

      setLoading(true);
      // Transformar items al formato esperado por backend si es necesario
      const payload = {
        mesa_id: pedido.mesa_id,
        cliente: pedido.cliente,
        items: pedido.items.map(it => ({ producto_id: it.producto_id, cantidad: it.cantidad })),
        notas: pedido.notas,
      };
      await updatePedido(pedidoId, payload);
      setLoading(false);
      navigate('/mesas');
    } catch (err) {
      console.error('Error al actualizar pedido:', err);
      setError(err?.response?.data?.error || 'Error al guardar la edición del pedido.');
      setLoading(false);
    }
  };

  if (!pedidoId) {
    return (
      <div className="toma-pedidos-container">
        <h1 className="toma-pedidos-title">Editar Pedido</h1>
        <p className="toma-pedidos-description">Ingresa desde Mesas y selecciona “Editar”. También puedes navegar a /pedidos/editar/ID.</p>
        <div style={{ marginTop: '1rem' }}>
          <Button onClick={() => navigate('/mesas')} variant="secondary">Ir a Mesas</Button>
        </div>
      </div>
    );
  }

  if (loading) return <div className="loader-container"><Spinner /></div>;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="toma-pedidos-container">
      <h1 className="toma-pedidos-title">Editar Pedido #{pedidoId}</h1>
      <p className="toma-pedidos-description">Ajusta productos y guarda los cambios.</p>
      <div className="pedidos-layout">
        <PedidoForm 
          productos={productos}
          mesas={mesas}
          seleccionarMesa={seleccionarMesa}
          agregarItem={agregarItem}
          pedidoActual={pedido}
        />
        <PedidoResumen 
          pedidoActual={pedido}
          eliminarItem={removeProducto}
          limpiarPedido={limpiarPedido}
          enviarPedido={saveEdicion}
        />
      </div>
    </div>
  );
}

export default PedidoEditar;