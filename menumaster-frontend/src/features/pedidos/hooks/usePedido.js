import { useState, useEffect, useCallback } from 'react';
import pedidoService from '../services/pedidoService';

// --- Funciones de Ayuda para la Impresión ---
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
    ventanaImpresion.document.write(`<pre>${contenido}</pre>`);
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

    // --- Carga de Datos ---
    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await pedidoService.getTomaPedidoData();
                setProductos(data.productos || []);
                setMesas(data.mesas || []);
            } catch (err) {
                setError('No se pudieron cargar los datos para tomar pedidos.');
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, []);

    // --- Acciones ---
    const seleccionarMesa = (mesaId) => {
        setPedidoActual(prev => ({ ...prev, mesa_id: mesaId }));
    };

    const agregarItem = (producto) => {
        setPedidoActual(prev => {
            const items = [...prev.items];
            const itemExistente = items.find(item => item.producto_id === producto.id);
            if (itemExistente) {
                itemExistente.cantidad += 1;
            } else {
                items.push({ producto_id: producto.id, nombre: producto.nombre, cantidad: 1, precio: producto.precio });
            }
            return { ...prev, items };
        });
    };
    
    const eliminarItem = (productoId) => {
        setPedidoActual(prev => ({
            ...prev,
            items: prev.items.filter(item => item.producto_id !== productoId)
        }));
    };

    const limpiarPedido = () => {
        setPedidoActual({ mesa_id: '', items: [], notas: '' });
    };

    const enviarPedido = async () => {
        if (!pedidoActual.mesa_id || pedidoActual.items.length === 0) {
            alert('Por favor, selecciona una mesa y agrega al menos un producto.');
            return;
        }
        try {
            setLoading(true);
            const pedidoCreado = await pedidoService.createPedido(pedidoActual);
            // ¡AQUÍ SE CONSERVA LA LÓGICA DE IMPRESIÓN!
            generarTicketCocina(pedidoCreado);
            alert('Pedido enviado a cocina exitosamente.');
            limpiarPedido();
        } catch (err) {
            alert('Error al enviar el pedido.');
        } finally {
            setLoading(false);
        }
    };
    
    // Devolvemos todo lo que los componentes necesitan
    return {
        loading, error, productos, mesas, pedidoActual,
        seleccionarMesa, agregarItem, eliminarItem, limpiarPedido, enviarPedido
    };
};
