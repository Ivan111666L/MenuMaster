import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import QRCode from 'qrcode'; // Asegúrate de haber instalado: npm install qrcode
import { useNotifications } from '@/hooks/useNotifications';
import facturacionService from '@/features/facturacion/services/facturacionService';
import api from '@/services/api';

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
            if (!Array.isArray(data)) {
                console.warn('useFacturacion: getPedidosParaFacturar no devolvió un arreglo. Valor recibido:', data);
                setPedidos([]);
            } else {
                setPedidos(data);
            }
        } catch (err) {
            setError('No se pudieron cargar los pedidos listos para facturar.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        cargarPedidos();
    }, [cargarPedidos]);

    // Auto-refresco por polling para datos en tiempo real
    useEffect(() => {
        const interval = setInterval(() => {
            cargarPedidos();
        }, 10000); // cada 10 segundos
        return () => clearInterval(interval);
    }, [cargarPedidos]);

    // Refrescar lista cuando se emita el evento global de pedidos
    useEffect(() => {
        const handler = () => cargarPedidos();
        window.addEventListener('pedidos:update', handler);
        return () => window.removeEventListener('pedidos:update', handler);
    }, [cargarPedidos]);

    // --- Acciones del Usuario ---
    const seleccionarPedido = async (pedido) => {
        // Establecemos el pedido seleccionado inicialmente (resumen)
        setPedidoSeleccionado(pedido);
        setNumeroPersonas(1);
        setQrCodeDataUrl('');

        // Intentamos cargar detalles completos del pedido desde el backend
        try {
            const res = await api.get(`/pedidos/${pedido.id}`);
            const detalles = res?.data?.data ?? res?.data ?? null;
            if (detalles) {
                setPedidoSeleccionado(detalles);
            }
        } catch (e) {
            // Si falla, mantenemos el resumen ya seleccionado
            console.warn('No se pudieron cargar los detalles completos del pedido:', e?.message || e);
        }
    };

    // Función unificada para facturar y recargar la lista
    const facturarYRecargar = async (pedidoId, metodoPago, metodoId = null) => {
        try {
            const datosPago = { metodo_pago: metodoPago, dividir: numeroPersonas > 1, personas: numeroPersonas };
            if (metodoId) datosPago.metodo_id = metodoId;
            await facturacionService.facturarPedido(pedidoId, datosPago);
            showSuccess(`Pedido #${pedidoId} facturado con ${metodoPago}.`);
            setPedidoSeleccionado(null);
            await cargarPedidos();
            // Permanecer en la misma página para continuar facturando
        } catch (err) {
            showError('Error al facturar el pedido.');
        }
    };

    // Función para generar la factura para imprimir
  const generarFacturaParaImprimir = () => {
      if (!pedidoSeleccionado) return;
        // Normalizamos items/detalles para evitar errores
        const rawItems = Array.isArray(pedidoSeleccionado.items)
            ? pedidoSeleccionado.items
            : Array.isArray(pedidoSeleccionado.detalles)
                ? pedidoSeleccionado.detalles
                : [];
        const getNombre = (item) => item?.nombre_producto ?? item?.producto_nombre ?? item?.nombre ?? 'Producto';
        const getCantidad = (item) => Number(item?.cantidad ?? 1);
        const getPrecioUnitario = (item) => Number(item?.precio_unitario ?? item?.precio ?? 0);

        const totalPedido = rawItems.reduce((sum, item) => sum + (getCantidad(item) * getPrecioUnitario(item)), 0);
        const totalPorPersona = totalPedido / Math.max(1, Number(numeroPersonas) || 1);

        let facturaTexto = "========================================\n";
        facturaTexto += "            FACTURA CLIENTE\n";
        facturaTexto += "========================================\n";
        facturaTexto += `Pedido: #${pedidoSeleccionado.id}\n`;
        facturaTexto += `Mesa: ${pedidoSeleccionado.mesa_numero}\n`;
        facturaTexto += `Fecha: ${new Date().toLocaleString()}\n`;
        facturaTexto += "----------------------------------------\n";
        facturaTexto += "Cant.  Producto              Subtotal\n";
        facturaTexto += "----------------------------------------\n";

        rawItems.forEach(item => {
            const nombre = getNombre(item).toString().padEnd(20, ' ').substring(0, 20);
            const cantidad = getCantidad(item).toString().padStart(3, ' ');
            const subtotal = (getPrecioUnitario(item) * getCantidad(item)).toFixed(2).padStart(8, ' ');
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
        // La impresión de factura no debe disparar facturación automática en backend
    };

    // Función para generar el código QR
  const generarPagoQR = async () => {
      if (!pedidoSeleccionado) return;
        
        const rawItems = Array.isArray(pedidoSeleccionado.items)
            ? pedidoSeleccionado.items
            : Array.isArray(pedidoSeleccionado.detalles)
                ? pedidoSeleccionado.detalles
                : [];
        const getCantidad = (item) => Number(item?.cantidad ?? 1);
        const getPrecioUnitario = (item) => Number(item?.precio_unitario ?? item?.precio ?? 0);
        const totalPedido = rawItems.reduce((sum, item) => sum + (getCantidad(item) * getPrecioUnitario(item)), 0);
        const totalPorPersona = totalPedido / Math.max(1, Number(numeroPersonas) || 1);
        
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
