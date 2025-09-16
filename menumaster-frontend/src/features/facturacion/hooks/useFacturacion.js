import { useState, useEffect, useCallback } from 'react';
import QRCode from 'qrcode'; // Librería para generar QR
import facturacionService from '../services/facturacionService'; // Nuestro servicio de API

// --- Función de ayuda para la impresión ---
const imprimirContenido = (contenido) => {
    const ventanaImpresion = window.open('', '_blank');
    // Usamos <pre> para respetar los espacios y saltos de línea del texto
    ventanaImpresion.document.write(`<pre>${contenido}</pre>`);
    ventanaImpresion.document.close();
    ventanaImpresion.focus();
    ventanaImpresion.print();
    ventanaImpresion.close();
};

export const useFacturacion = () => {
    // --- Estados del Hook ---
    const [pedidos, setPedidos] = useState([]);
    const [pedidoSeleccionado, setPedidoSeleccionado] = useState(null);
    const [numeroPersonas, setNumeroPersonas] = useState(1);
    const [qrCodeDataUrl, setQrCodeDataUrl] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // --- Carga de Datos desde la API ---
    const cargarPedidos = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);
            const data = await facturacionService.getPedidosParaFacturar();
            setPedidos(data);
        } catch (err) {
            setError('No se pudieron cargar los pedidos listos para facturar.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        cargarPedidos();
    }, [cargarPedidos]);

    // --- Acciones del Usuario ---
    const seleccionarPedido = (pedido) => {
        setPedidoSeleccionado(pedido);
        setNumeroPersonas(1);
        setQrCodeDataUrl('');
    };

    // CORRECCIÓN: Esta función ahora se conecta con el backend para facturar
    const facturar = async (metodoPago) => {
        if (!pedidoSeleccionado) return;
        try {
            const datosPago = {
                metodo_pago: metodoPago,
                dividir: numeroPersonas > 1,
                personas: numeroPersonas,
            };
            await facturacionService.facturarPedido(pedidoSeleccionado.id, datosPago);
            
            // Si el método es 'Imprimir', se genera el texto de la factura
            if (metodoPago === 'Imprimir') {
                generarTextoFactura();
            }

            alert(`Pedido ${pedidoSeleccionado.id} facturado con ${metodoPago}.`);
            setPedidoSeleccionado(null);
            cargarPedidos(); // Recarga la lista de pedidos pendientes

        } catch (err) {
            alert('Error al facturar el pedido.');
        }
    };
    
    // Tu lógica de generación de texto para imprimir, ahora como una función de ayuda
    const generarTextoFactura = () => {
        if (!pedidoSeleccionado) return;

        const totalPedido = pedidoSeleccionado.items.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
        const totalPorPersona = totalPedido / numeroPersonas;

        let facturaTexto = "========================================\n";
        facturaTexto += "            FACTURA CLIENTE\n";
        facturaTexto += "========================================\n";
        facturaTexto += `Pedido: #${pedidoSeleccionado.id}\n`;
        facturaTexto += `Mesa: ${pedidoSeleccionado.mesa_numero}\n`;
        facturaTexto += `Fecha: ${new Date().toLocaleString()}\n`;
        facturaTexto += "----------------------------------------\n";
        facturaTexto += "Cant.  Producto              Subtotal\n";
        facturaTexto += "----------------------------------------\n";

        pedidoSeleccionado.items.forEach(item => {
            const nombre = item.nombre_producto.padEnd(20, ' ').substring(0, 20);
            const cantidad = item.cantidad.toString().padStart(3, ' ');
            const subtotal = (item.precio_unitario * item.cantidad).toFixed(2).padStart(8, ' ');
            facturaTexto += `${cantidad}    ${nombre}  $${subtotal}\n`;
        });

        facturaTexto += "----------------------------------------\n";
        facturaTexto += `TOTAL: $${totalPedido.toFixed(2)}\n`;
        facturaTexto += "========================================\n\n";

        if (numeroPersonas > 1) {
            facturaTexto += `Dividido entre ${numeroPersonas} personas\n`;
            facturaTexto += `TOTAL POR PERSONA: $${totalPorPersona.toFixed(2)}\n`;
            facturaTexto += "========================================\n";
        }

        imprimirContenido(facturaTexto);
    };

    // Tu lógica para generar el QR
    const generarPagoQR = async () => {
        if (!pedidoSeleccionado) return;
    
        const totalPedido = pedidoSeleccionado.items.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
        const totalPorPersona = totalPedido / numeroPersonas;
        
        // En una app real, aquí pondrías un enlace a tu pasarela de pago.
        const textoQR = `https://tu-pago.com/pagar?monto=${totalPorPersona.toFixed(2)}&ref=PED-${pedidoSeleccionado.id}`;
    
        try {
            const dataUrl = await QRCode.toDataURL(textoQR);
            setQrCodeDataUrl(dataUrl);
            // También podrías marcarlo como facturado aquí
            // await facturar('QR'); 
        } catch (err) {
            console.error("Error generando el código QR", err);
            alert("Hubo un error al generar el código QR.");
        }
    };

    // --- Devolvemos los estados y funciones que usarán los componentes ---
    return {
        loading,
        error,
        pedidos,
        pedidoSeleccionado,
        seleccionarPedido,
        numeroPersonas,
        setNumeroPersonas,
        facturar, // Se usa para facturar con Efectivo y Tarjeta
        generarPagoQR, // Función específica para el QR
        qrCodeDataUrl,
    };
};