import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getPedidoById, createPedido, updatePedido } from '@/services/pedidosService';
import { getMesas } from '@/services/mesasService';
import { getProductos } from '@/services/productosService';

export function usePedido(pedidoId = null) {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Estados para el pedido
  const [pedido, setPedido] = useState({
    mesa_id: '',
    cliente: '',
    estado: 'pendiente',
    items: [],
    total: 0
  });
  
  // Estados para los datos de referencia
  const [mesas, setMesas] = useState([]);
  const [productos, setProductos] = useState([]);
  
  // Cargar datos iniciales
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        
        // Cargar mesas disponibles
        const mesasData = await getMesas();
        setMesas(mesasData);
        
        // Cargar productos disponibles
        const productosData = await getProductos();
        setProductos(productosData);
        
        // Si hay un ID de pedido, cargar ese pedido
        if (pedidoId) {
          const pedidoData = await getPedidoById(pedidoId);
          setPedido(pedidoData);
        }
        
        setLoading(false);
      } catch (err) {
        console.error('Error al cargar datos:', err);
        setError('Error al cargar los datos necesarios. Por favor, intenta de nuevo.');
        setLoading(false);
      }
    };
    
    fetchData();
  }, [pedidoId]);
  
  // Manejar cambios en el pedido
  const handleChangeMesa = (mesaId) => {
    setPedido(prev => ({ ...prev, mesa_id: mesaId }));
  };
  
  const handleChangeCliente = (cliente) => {
    setPedido(prev => ({ ...prev, cliente }));
  };
  
  // Agregar un producto al pedido
  const addProducto = (producto, cantidad = 1) => {
    setPedido(prev => {
      // Verificar si el producto ya está en el pedido
      const existingItemIndex = prev.items.findIndex(item => item.producto_id === producto.id);
      
      let newItems;
      if (existingItemIndex >= 0) {
        // Actualizar cantidad si ya existe
        newItems = [...prev.items];
        newItems[existingItemIndex] = {
          ...newItems[existingItemIndex],
          cantidad: newItems[existingItemIndex].cantidad + cantidad
        };
      } else {
        // Agregar nuevo item si no existe
        newItems = [
          ...prev.items,
          {
            producto_id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            cantidad
          }
        ];
      }
      
      // Calcular nuevo total
      const total = newItems.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
      
      return {
        ...prev,
        items: newItems,
        total
      };
    });
  };
  
  // Eliminar un producto del pedido
  const removeProducto = (productoId) => {
    setPedido(prev => {
      const newItems = prev.items.filter(item => item.producto_id !== productoId);
      const total = newItems.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
      
      return {
        ...prev,
        items: newItems,
        total
      };
    });
  };
  
  // Actualizar cantidad de un producto
  const updateCantidad = (productoId, cantidad) => {
    if (cantidad <= 0) {
      removeProducto(productoId);
      return;
    }
    
    setPedido(prev => {
      const newItems = prev.items.map(item => 
        item.producto_id === productoId 
          ? { ...item, cantidad } 
          : item
      );
      
      const total = newItems.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
      
      return {
        ...prev,
        items: newItems,
        total
      };
    });
  };
  
  // Guardar el pedido
  const savePedido = async () => {
    try {
      setLoading(true);
      
      if (!pedido.mesa_id) {
        setError('Debes seleccionar una mesa');
        setLoading(false);
        return;
      }
      
      if (pedido.items.length === 0) {
        setError('El pedido debe tener al menos un producto');
        setLoading(false);
        return;
      }
      
      let result;
      if (pedidoId) {
        result = await updatePedido(pedidoId, pedido);
      } else {
        result = await createPedido(pedido);
      }
      
      setLoading(false);
      navigate('/pedidos/lista', { state: { message: 'Pedido guardado con éxito' } });
      return result;
    } catch (err) {
      console.error('Error al guardar pedido:', err);
      setError('Error al guardar el pedido. Por favor, intenta de nuevo.');
      setLoading(false);
      throw err;
    }
  };
  
  return {
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
  };
}