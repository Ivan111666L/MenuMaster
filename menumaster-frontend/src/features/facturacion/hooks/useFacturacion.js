import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import QRCode from 'qrcode'; // Asegúrate de haber instalado: npm install qrcode
import { useNotifications } from '@/hooks/useNotifications';
import facturacionService from '@/features/facturacion/services/facturacionService';

// --- Función de ayuda para la impresión ---
const imprimirTicket = (contenido) => {
  const win = window.open('', '_blank');
  win.document.write(contenido);
  win.print();
  win.close();
};

export const useFacturacion = () => {
    // Hook de notificaciones
    const { showSuccess, showError } = useNotifications();
    const navigate = useNavigate();
    
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

    // Refrescar lista cuando se emita el evento global de pedidos
    useEffect(() => {
        const handler = () => cargarPedidos();
        window.addEventListener('pedidos:update', handler);
        return () => window.removeEventListener('pedidos:update', handler);
    }, [cargarPedidos]);

    // --- Acciones del Usuario ---
    const seleccionarPedido = (pedido) => {
        setPedidoSeleccionado(pedido);
        setNumeroPersonas(1);
        setQrCodeDataUrl('');
    };

    // Función unificada para facturar y recargar la lista
    const facturarYRecargar = async (pedidoId, metodoPago) => {
        try {
            const datosPago = { metodo_pago: metodoPago, dividir: numeroPersonas > 1, personas: numeroPersonas };
            await facturacionService.facturarPedido(pedidoId, datosPago);
            showSuccess(`Pedido #${pedidoId} facturado con ${metodoPago}.`);
            setPedidoSeleccionado(null);
            await cargarPedidos();
            // Navegar a la pantalla de pagos según la ruta solicitada
            navigate('/facturacion/pagos');
        } catch (err) {
            showError('Error al facturar el pedido.');
        }
    };

    // Función para generar la factura para imprimir
    const generarFacturaParaImprimir = () => {
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

        imprimirTicket(facturaTexto);
        // Marcamos el pedido como facturado en el backend después de imprimir
        facturarYRecargar(pedidoSeleccionado.id, 'Impreso');
    };

    // Función para generar el código QR
    const generarPagoQR = async () => {
        if (!pedidoSeleccionado) return;
    
        const totalPedido = pedidoSeleccionado.items.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
        const totalPorPersona = totalPedido / numeroPersonas;
        
        const textoQR = `https://tu-pasarela-de-pago.com/pagar?monto=${totalPorPersona.toFixed(2)}&ref=PED-${pedidoSeleccionado.id}`;
    
        try {
            const dataUrl = await QRCode.toDataURL(textoQR);
            setQrCodeDataUrl(dataUrl);
            // Opcional: podrías marcarlo como facturado aquí también
            // await facturarYRecargar(pedidoSeleccionado.id, 'QR');
        } catch (err) {
            showError("Hubo un error al generar el código QR.");
        }
    };

    // Devolvemos todos los estados y funciones que usarán los componentes
    return {
        loading,
        error,
        pedidos,
        pedidoSeleccionado,
        seleccionarPedido,
        numeroPersonas,
        setNumeroPersonas,
        facturar: facturarYRecargar,
        generarFacturaParaImprimir,
        generarPagoQR,
        qrCodeDataUrl
    };
};
