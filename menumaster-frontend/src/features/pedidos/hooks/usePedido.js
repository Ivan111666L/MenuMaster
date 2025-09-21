import { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify'; // Importamos la librería de notificaciones
import pedidoService from '@/features/pedidos/services/pedidoService';

// --- Funciones de Ayuda para la Impresión (se mantienen intactas) ---
const generarTicketCocina = (pedidoCompleto) => {
    let ticket = "================================\n";
    ticket += "      NUEVO PEDIDO PARA COCINA\n";
    ticket += "================================\n";
    ticket += `Mesa: ${pedidoCompleto.mesa_numero}\n`;
    ticket += `Pedido: #${pedidoCompleto.id}\n`;
    ticket += `Hora: ${new Date(pedidoCompleto.fecha_creacion).toLocaleTimeString()}\n`;
    ticket += "--------------------------------\n";

    pedidoCompleto.items.forEach(item => {
        ticket += `${item.cantidad} x ${item.nombre_producto || item.nombre} `;
        if (item.notas) {
            ticket += ` [Notas: ${item.notas}]`;
        }
        ticket += "\n";
    });

    if (pedidoCompleto.notas) {
        ticket += "--------------------------------\n";
        ticket += `NOTAS GENERALES: ${pedidoCompleto.notas}\n`;
    }
    ticket += "================================\n";
    imprimirContenido(ticket);
};

const imprimirContenido = (contenido) => {
    const ventanaImpresion = window.open('', '_blank');
    ventanaImpresion.document.write(`<pre style="font-family: monospace; font-size: 12px;">${contenido}</pre>`);
    ventanaImpresion.document.close();
    ventanaImpresion.focus();
    ventanaImpresion.print();
    ventanaImpresion.close();
};
// ---------------------------------------------

export const usePedido = () => {
    // --- Estados ---
    const [productos, setProductos] = useState([]);
    const [mesas, setMesas] = useState([]);
    const [pedidoActual, setPedidoActual] = useState({ mesa_id: '', items: [], notas: '' });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [ticketHtml, setTicketHtml] = useState(null);
    const [ticketOpen, setTicketOpen] = useState(false);

    // --- Carga de Datos ---
    const fetchData = useCallback(async () => {
        try {
            const data = await pedidoService.getTomaPedidoData();
            // Las mesas ya vienen filtradas como disponibles desde el backend
            setProductos(data.productos || []);
            setMesas(data.mesas || []);
        } catch (err) {
            setError('No se pudieron cargar los datos para tomar pedidos.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    // --- Acciones ---
    const handleChangeMesa = (mesaId) => {
        setPedidoActual(prev => ({ ...prev, mesa_id: mesaId }));
    };

    const handleChangeCliente = (cliente) => {
        setPedidoActual(prev => ({ ...prev, cliente }));
    };

    const addProducto = (producto) => {
        setPedidoActual(prev => {
            const items = [...prev.items];
            const itemExistente = items.find(item => item.producto_id === producto.id);
            if (itemExistente) {
                itemExistente.cantidad += 1;
            } else {
                items.push({ 
                    producto_id: producto.id, 
                    nombre: producto.nombre, 
                    cantidad: 1, 
                    precio: producto.precio 
                });
            }
            return { ...prev, items };
        });
    };

    const removeProducto = (productoId) => {
        setPedidoActual(prev => ({
            ...prev,
            items: prev.items.filter(item => item.producto_id !== productoId)
        }));
    };

    const updateCantidad = (productoId, cantidad) => {
        setPedidoActual(prev => ({
            ...prev,
            items: prev.items.map(item => 
                item.producto_id === productoId 
                    ? { ...item, cantidad: Math.max(1, cantidad) }
                    : item
            )
        }));
<<<<<<< HEAD
    };

    const savePedido = async () => {
        if (!pedidoActual.mesa_id || pedidoActual.items.length === 0) {
            toast.warn('Por favor, selecciona una mesa y agrega al menos un producto.');
            return;
        }
        try {
            setLoading(true);
            const pedidoCreado = await pedidoService.createPedido(pedidoActual);
            // Obtener el ticket HTML desde el backend
            const ticketData = await pedidoService.getPedidoTicket(pedidoCreado.id);
            setTicketHtml(ticketData.html);
            setTicketOpen(true);
            toast.success('¡Pedido enviado a cocina exitosamente!');
            setPedidoActual({ mesa_id: '', items: [], notas: '' });
        } catch (err) {
            toast.error(err.response?.data?.error || 'Error al enviar el pedido.');
        } finally {
            setLoading(false);
        }
    };
    
    const eliminarItem = (productoId) => {
        removeProducto(productoId);
    };

    const limpiarPedido = () => {
        setPedidoActual({ mesa_id: '', items: [], notas: '' });
    };

    const enviarPedido = async () => {
        await savePedido();
=======
    };

    const savePedido = async () => {
        if (!pedidoActual.mesa_id || pedidoActual.items.length === 0) {
            toast.warn('Por favor, selecciona una mesa y agrega al menos un producto.');
            return;
        }
        try {
            setLoading(true);
            const pedidoCreado = await pedidoService.createPedido(pedidoActual);
            
            // Se conserva la lógica de impresión
            generarTicketCocina(pedidoCreado);
            
            toast.success('¡Pedido enviado a cocina exitosamente!');
            setPedidoActual({ mesa_id: '', items: [], notas: '' });
        } catch (err) {
            toast.error(err.response?.data?.error || 'Error al enviar el pedido.');
        } finally {
            setLoading(false);
        }
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
    };
    
    const eliminarItem = (productoId) => {
        removeProducto(productoId);
    };

    const limpiarPedido = () => {
        setPedidoActual({ mesa_id: '', items: [], notas: '' });
    };

    const enviarPedido = async () => {
        await savePedido();
    };
    
    // Devolvemos todo lo que los componentes necesitan
    return {
        pedido: pedidoActual,
        loading, 
        error, 
        productos, 
        mesas, 
        handleChangeMesa, 
        handleChangeCliente, 
        addProducto, 
        removeProducto, 
        updateCantidad, 
        savePedido,
        // Mantenemos compatibilidad con nombres antiguos
        pedidoActual,
        seleccionarMesa: handleChangeMesa,
        agregarItem: addProducto,
        eliminarItem,
        limpiarPedido,
<<<<<<< HEAD
        enviarPedido,
        ticketHtml,
        ticketOpen,
        setTicketOpen
=======
        enviarPedido
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
    };
};
