import React, { useState, useEffect } from 'react';
import pedidoService from '@/features/pedidos/services/pedidoService';
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

    const crearPedido = async () => {
        if (!pedido.mesa_id || pedido.items.length === 0) {
            setError('Selecciona una mesa y agrega al menos un producto');
            return;
        }

        setLoading(true);
        setError('');
        setSuccess('');

        try {
            const resultado = await pedidoService.createPedido(pedido);
            setSuccess('¡Pedido creado exitosamente! ID: ' + resultado.id);
            setPedido({ mesa_id: '', items: [], notas: '' });
        } catch (err) {
            setError('Error al crear pedido: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    const calcularTotal = () => {
        return pedido.items.reduce((total, item) => total + (item.precio * item.cantidad), 0).toFixed(2);
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
                        <select 
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
                        <textarea
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
                    </div>
                </div>

                <div style={{ marginTop: '2rem', padding: '1rem', background: '#f5f5f5', borderRadius: '4px' }}>
                    <h4>Debug Info:</h4>
                    <pre style={{ fontSize: '0.8rem' }}>
                        {JSON.stringify({ 
                            mesasCount: mesas.length, 
                            productosCount: productos.length,
                            pedido 
                        }, null, 2)}
                    </pre>
                </div>
            </div>
        </div>
    );
};

export default TestPedidos;