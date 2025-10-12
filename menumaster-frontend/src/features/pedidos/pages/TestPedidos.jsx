import React, { useState, useEffect } from 'react';
import pedidoService from '@/features/pedidos/services/pedidoService';
import { generarTicketHTMLMinimo } from '@/features/pedidos/services/imprimirPedidoService';
import facturacionService from '@/features/facturacion/services/facturacionService';
import mesaService from '@/features/mesas/services/mesaService';
import { ESTADOS_MESA, ESTADOS_PEDIDO } from '@/utils/constant.js';
import { getMesaById } from '@/services/mesasService';
import { getMesasDisponibles } from '@/services/mesasService';
import { getProductos } from '@/services/productosService';
import '@/styles/pedidos.css';

const TestPedidos = () => {
    const [mesas, setMesas] = useState([]);
    const [productos, setProductos] = useState([]);
    const [pedido, setPedido] = useState({
        mesa_id: '',
        items: [],
        notas: ''
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const [ultimoPedidoId, setUltimoPedidoId] = useState(null);
    const [ultimoMesaId, setUltimoMesaId] = useState(null);
    const [estadoMesaActual, setEstadoMesaActual] = useState(null);

    useEffect(() => {
        const cargarDatos = async () => {
            try {
                const [mesasData, productosData] = await Promise.all([
                    getMesasDisponibles(),
                    getProductos()
                ]);
                setMesas(mesasData);
                setProductos(productosData);
            } catch (err) {
                setError('Error al cargar datos: ' + err.message);
            }
        };
        cargarDatos();
    }, []);

    const agregarProducto = (producto) => {
        setPedido(prev => {
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

    const eliminarProducto = (productoId) => {
        setPedido(prev => ({
            ...prev,
            items: prev.items.filter(item => item.producto_id !== productoId)
        }));
    };

    const actualizarEstadoMesa = async (mesaId) => {
        if (!mesaId) return;
        try {
            const res = await getMesaById(mesaId);
            const mesaData = res?.data ?? res;
            const estado = mesaData?.estado ?? mesaData?.estado_nombre ?? null;
            const estadoNormalizado = (() => {
                const e = String(estado || '').toLowerCase();
                if ([ESTADOS_MESA.DISPONIBLE, ESTADOS_MESA.OCUPADA, ESTADOS_MESA.RESERVADA].includes(e)) return e;
                return estado;
            })();
            setEstadoMesaActual(estadoNormalizado);
        } catch (e) {
            console.warn('Error al obtener estado de mesa:', e);
        }
    };

    const crearPedido = async () => {
        if (!pedido.mesa_id || pedido.items.length === 0) {
            setError('Selecciona una mesa y agrega al menos un producto');
            return;
        }

        setLoading(true);
        setError('');
        setSuccess('');

        try {
            // Ajustar payload al formato esperado por backend
            const payload = {
                mesa_id: pedido.mesa_id,
                notas: pedido.notas || '',
                items: pedido.items.map((it) => ({
                    producto_id: it.producto_id ?? it.id,
                    cantidad: it.cantidad,
                    notas: it.notas || undefined,
                }))
            };
            const resultado = await pedidoService.createPedido(payload);
            setSuccess('¡Pedido creado exitosamente! ID: ' + resultado.id);
            setUltimoPedidoId(resultado?.id ?? resultado?.pedido_id ?? null);
            setUltimoMesaId(payload.mesa_id);
            // Verificar ocupación de la mesa tras crear
            await actualizarEstadoMesa(payload.mesa_id);
            // Generar ticket mínimo y abrir ventana de impresión
            try {
                const html = await generarTicketHTMLMinimo(resultado.id);
                const printWindow = window.open('', 'PRINT', 'height=600,width=400');
                if (printWindow) {
                    printWindow.document.write('<html><head><title>Ticket Cocina</title></head><body>');
                    printWindow.document.write(html);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.focus();
                    // Pequeño delay para asegurar render antes de imprimir
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 150);
                }
            } catch (e) {
                console.warn('No se pudo generar/imprimir ticket mínimo:', e);
            }
            setPedido({ mesa_id: '', items: [], notas: '' });
        } catch (err) {
            setError('Error al crear pedido: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    const facturarPedidoActual = async () => {
        if (!ultimoPedidoId) {
            setError('No hay un pedido reciente para facturar.');
            return;
        }
        setLoading(true);
        setError('');
        try {
            await facturacionService.facturarPedido(ultimoPedidoId, { metodo_pago: 'efectivo' });
            setSuccess(`Pedido #${ultimoPedidoId} facturado.`);
            // Verificar liberación de mesa tras facturar
            await actualizarEstadoMesa(ultimoMesaId);
        } catch (err) {
            setError('Error al facturar pedido: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    const calcularTotal = () => {
        return pedido.items.reduce((total, item) => total + (item.precio * item.cantidad), 0).toFixed(2);
    };

    const ejecutarPruebaAutomatica = async () => {
        setLoading(true);
        setError('');
        setSuccess('');
        try {
            // 1) Obtener mesas disponibles; si no hay, intentar liberar todas
            let mesasDisponibles = await getMesasDisponibles();
            if (!Array.isArray(mesasDisponibles)) mesasDisponibles = [];
            if (mesasDisponibles.length === 0) {
                try {
                    await mesaService.resetAllMesas();
                } catch {}
                mesasDisponibles = await getMesasDisponibles();
            }
            if (!mesasDisponibles || mesasDisponibles.length === 0) {
                throw new Error('No hay mesas disponibles incluso después del reset.');
            }

            const mesaSeleccionada = mesasDisponibles[0];

            // 2) Obtener productos si no están cargados
            let productosData = productos;
            if (!Array.isArray(productosData) || productosData.length === 0) {
                productosData = await getProductos();
            }
            if (!Array.isArray(productosData) || productosData.length === 0) {
                throw new Error('No hay productos disponibles para crear el pedido.');
            }

            // 3) Crear pedido con 1-2 productos
            const items = productosData.slice(0, Math.min(2, productosData.length)).map(p => ({
                producto_id: p.id,
                cantidad: 1,
            }));

            const payload = {
                mesa_id: mesaSeleccionada.id,
                notas: 'Prueba automática',
                items,
            };
            const resultado = await pedidoService.createPedido(payload);
            const nuevoPedidoId = resultado?.id ?? resultado?.pedido_id ?? null;
            setUltimoPedidoId(nuevoPedidoId);
            setUltimoMesaId(payload.mesa_id);
            await actualizarEstadoMesa(payload.mesa_id);

            // 4) Generar ticket mínimo (si disponible)
            try {
                const html = await generarTicketHTMLMinimo(nuevoPedidoId);
                const printWindow = window.open('', 'PRINT', 'height=600,width=400');
                if (printWindow) {
                    printWindow.document.write('<html><head><title>Ticket Cocina</title></head><body>');
                    printWindow.document.write(html);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.focus();
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 150);
                }
            } catch (e) {
                console.warn('No se pudo generar/imprimir ticket mínimo (flujo automático):', e);
            }

            // 5) Facturar pedido
            await facturacionService.facturarPedido(nuevoPedidoId, { metodo_pago: 'efectivo' });
            await actualizarEstadoMesa(payload.mesa_id);

            // Notificar vistas para refrescar estado
            try {
                window.dispatchEvent(new CustomEvent('mesas:update', { detail: { source: 'test-auto', pedidoId: nuevoPedidoId } }));
                window.dispatchEvent(new CustomEvent('pedidos:update', { detail: { source: 'test-auto', pedidoId: nuevoPedidoId } }));
            } catch {}

            setSuccess(`Flujo automático completado: Pedido #${nuevoPedidoId} facturado en Mesa ${mesaSeleccionada.numero ?? mesaSeleccionada.id}.`);
        } catch (err) {
            setError('Error en prueba automática: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="app-container">
            <div className="productos-form-wrapper">
                <h1 className="productos-title">Test Crear Pedido</h1>
                <p className="productos-description">Prueba de funcionalidad de creación de pedidos</p>
                
                {error && <div style={{ color: 'red', margin: '1rem 0' }}>{error}</div>}
                {success && <div style={{ color: 'green', margin: '1rem 0' }}>{success}</div>}

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '2rem' }}>
                    {/* Columna izquierda - Selección */}
                    <div>
                        <h3>Seleccionar Mesa</h3>
                        <label htmlFor="mesa-test">Mesa</label>
                        <select 
                            id="mesa-test"
                            value={pedido.mesa_id} 
                            onChange={(e) => setPedido(prev => ({ ...prev, mesa_id: e.target.value }))}
                            className="form-input"
                        >
                            <option value="">-- Seleccionar Mesa --</option>
                            {mesas.map(mesa => (
                                <option key={mesa.id} value={mesa.id}>
                                    Mesa {mesa.numero} ({mesa.estado})
                                </option>
                            ))}
                        </select>

                        <h3 style={{ marginTop: '2rem' }}>Productos Disponibles</h3>
                        <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
                            {productos.map(producto => (
                                <div key={producto.id} style={{ 
                                    border: '1px solid #ccc', 
                                    padding: '0.5rem', 
                                    margin: '0.5rem 0',
                                    cursor: 'pointer'
                                }} onClick={() => agregarProducto(producto)}>
                                    <strong>{producto.nombre}</strong><br />
                                    Precio: ${producto.precio}<br />
                                    <small>{producto.descripcion}</small>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Columna derecha - Pedido actual */}
                    <div>
                        <h3>Pedido Actual</h3>
                        {pedido.items.length === 0 ? (
                            <p>No hay productos en el pedido</p>
                        ) : (
                            <div>
                                {pedido.items.map(item => (
                                    <div key={item.producto_id} style={{ 
                                        border: '1px solid #ddd', 
                                        padding: '0.5rem', 
                                        margin: '0.5rem 0',
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'center'
                                    }}>
                                        <div>
                                            <strong>{item.nombre}</strong><br />
                                            {item.cantidad} x ${item.precio} = ${(item.cantidad * item.precio).toFixed(2)}
                                        </div>
                                        <button 
                                            onClick={() => eliminarProducto(item.producto_id)}
                                            style={{ background: 'red', color: 'white', border: 'none', padding: '0.25rem 0.5rem' }}
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                ))}
                                <div style={{ marginTop: '1rem', fontWeight: 'bold' }}>
                                    Total: ${calcularTotal()}
                                </div>
                            </div>
                        )}

                        <h4 style={{ marginTop: '2rem' }}>Notas (opcional)</h4>
                        <label htmlFor="notas-test">Notas</label>
                        <textarea
                            id="notas-test"
                            value={pedido.notas}
                            onChange={(e) => setPedido(prev => ({ ...prev, notas: e.target.value }))}
                            className="form-input"
                            rows="3"
                            placeholder="Instrucciones especiales..."
                        />

                        <button
                            onClick={crearPedido}
                            disabled={loading || !pedido.mesa_id || pedido.items.length === 0}
                            style={{
                                width: '100%',
                                padding: '1rem',
                                marginTop: '1rem',
                                background: loading ? '#ccc' : '#007bff',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: loading ? 'not-allowed' : 'pointer'
                            }}
                        >
                            {loading ? 'Creando...' : 'Crear Pedido'}
                        </button>

                        <div style={{ marginTop: '1rem', padding: '0.75rem', background: '#eef', borderRadius: '4px' }}>
                            <p><strong>Estado actual de la mesa:</strong> {estadoMesaActual ?? 'desconocido'}</p>
                            <button
                                onClick={facturarPedidoActual}
                                disabled={loading || !ultimoPedidoId}
                                style={{
                                    width: '100%',
                                    padding: '0.75rem',
                                    marginTop: '0.5rem',
                                    background: loading || !ultimoPedidoId ? '#ccc' : '#28a745',
                                    color: 'white',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: loading || !ultimoPedidoId ? 'not-allowed' : 'pointer'
                                }}
                            >
                                {loading ? 'Procesando...' : 'Facturar Pedido Reciente'}
                            </button>
                            <button
                                onClick={ejecutarPruebaAutomatica}
                                disabled={loading}
                                style={{
                                    width: '100%',
                                    padding: '0.75rem',
                                    marginTop: '0.5rem',
                                    background: loading ? '#ccc' : '#6c63ff',
                                    color: 'white',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: loading ? 'not-allowed' : 'pointer'
                                }}
                            >
                                {loading ? 'Procesando...' : 'Ejecutar Prueba Automática'}
                            </button>
                        </div>
                    </div>
                </div>

                <div style={{ marginTop: '2rem', padding: '1rem', background: '#f5f5f5', borderRadius: '4px' }}>
                    <h4>Debug Info:</h4>
                    <pre style={{ fontSize: '0.8rem' }}>
                        {JSON.stringify({ 
                            mesasCount: mesas.length, 
                            productosCount: productos.length,
                            pedido,
                            ultimoPedidoId,
                            ultimoMesaId,
                            estadoMesaActual
                        }, null, 2)}
                    </pre>
                </div>
            </div>
        </div>
    );
};

export default TestPedidos;