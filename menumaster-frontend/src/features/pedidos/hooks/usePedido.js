import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useNotifications } from '@/hooks/useNotifications';
import pedidoService from '@/features/pedidos/services/pedidoService';
import { generarTicketHTML } from '@/features/pedidos/services/imprimirPedidoService';
import mesaService from '@/features/mesas/services/mesaService';

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
        ticket += `${item.cantidad} x ${item.nombre_producto}\n`;
    });

    if (pedidoCompleto.notas) {
        ticket += "--------------------------------\n";
        ticket += `NOTAS: ${pedidoCompleto.notas}\n`;
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
    // --- Hooks ---
    const { showWarning, showSuccess, showError } = useNotifications();
    const navigate = useNavigate();
    
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
    };

    const savePedido = async () => {
        if (!pedidoActual.mesa_id || pedidoActual.items.length === 0) {
            showWarning('Por favor, selecciona una mesa y agrega al menos un producto.');
            return;
        }
        try {
            setLoading(true);
            const pedidoCreado = await pedidoService.createPedido(pedidoActual);
            // Al crear el pedido, marcar la mesa como ocupada en la base de datos
            try {
                await mesaService.updateMesa(pedidoCreado.mesa_id, { estado_nombre: 'ocupada' });
            } catch (e) {
                console.warn('No se pudo actualizar el estado de la mesa a ocupada:', e);
            }
            // Generar el ticket HTML e imprimir automáticamente
            const html = await generarTicketHTML(pedidoCreado.id);
            setTicketHtml(html);
            setTicketOpen(false);
            // Abrir ventana de impresión
            const printWindow = window.open('', '_blank');
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();

            showSuccess('Pedido enviado e impreso. Redirigiendo a Facturación...');
            setPedidoActual({ mesa_id: '', items: [], notas: '' });
            // Notificar a la vista de Mesas para refrescar estado inmediatamente
            try {
                window.dispatchEvent(new CustomEvent('mesas:update', { detail: { source: 'pedido', pedidoId: pedidoCreado.id } }));
                // Notificar a Facturación para que aparezca el pedido automáticamente
                window.dispatchEvent(new CustomEvent('pedidos:update', { detail: { source: 'pedido', pedidoId: pedidoCreado.id } }));
            } catch {}
            // Redirigir a facturación
            navigate('/facturacion');
        } catch (err) {
            showError(err.response?.data?.error || 'Error al enviar el pedido.');
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
        enviarPedido,
        ticketHtml,
        ticketOpen,
        setTicketOpen
    };
};
