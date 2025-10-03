import React, { useState, useEffect } from 'react';
import productoService from '@/features/productos/services/productoService'; // Servicio de productos
import { getRentabilidadProductos } from '@/features/analisis/services/analisisService';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import Input from '@/components/Input';
import '@/styles/productos.css'; // Estilos dedicados para esta página

function ProductosCreados() {
    // --- 1. Estados para manejar los datos, la carga y los errores ---
    const [productos, setProductos] = useState([]);
    const [busqueda, setBusqueda] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [mostrarRentabilidad, setMostrarRentabilidad] = useState(false);
    const [productoSeleccionado, setProductoSeleccionado] = useState(null);
    const [detalleRentabilidad, setDetalleRentabilidad] = useState(null);
    const [cargandoRentabilidad, setCargandoRentabilidad] = useState(false);
    const [errorRentabilidad, setErrorRentabilidad] = useState(null);

    // --- 2. Carga de datos inicial con useEffect ---
    useEffect(() => {
        const cargarProductos = async () => {
            try {
                const data = await productoService.getProductos();
                setProductos(Array.isArray(data) ? data : []);
            } catch (err) {
                setError('No se pudieron cargar los productos.');
            } finally {
                setIsLoading(false);
            }
        };
        cargarProductos();
    }, []); // El array vacío asegura que se ejecute solo una vez al montar el componente

    // --- 3. Conexión de acciones a la API ---
    const handleDelete = async (id) => {
        if (window.confirm('¿Estás seguro de que quieres eliminar este producto?')) {
            try {
                await productoService.deleteProducto(id);
                // Si la eliminación en el backend es exitosa, actualizamos la UI al instante
                setProductos(prevProductos => prevProductos.filter(p => p.id !== id));
            } catch (err) {
                alert('Error al eliminar el producto.');
            }
        }
    };

    // Consultar y mostrar rentabilidad del producto seleccionado
    const handleVerRentabilidad = async (producto) => {
        setProductoSeleccionado(producto);
        setMostrarRentabilidad(true);
        setCargandoRentabilidad(true);
        setErrorRentabilidad(null);
        setDetalleRentabilidad(null);
        try {
            // Por defecto, usamos el último mes para el análisis
            const hoy = new Date();
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth() - 1, hoy.getDate());
            const fechaInicio = inicioMes.toISOString().split('T')[0];
            const fechaFin = hoy.toISOString().split('T')[0];

            const resp = await getRentabilidadProductos(fechaInicio, fechaFin);
            if (resp?.status === 'success' && Array.isArray(resp?.data)) {
                const encontrado = resp.data.find(p => parseInt(p.producto_id) === parseInt(producto.id) || p.producto_nombre === producto.nombre);
                if (encontrado) {
                    setDetalleRentabilidad(encontrado);
                } else {
                    setErrorRentabilidad('No se encontró información de rentabilidad para este producto en el período seleccionado.');
                }
            } else {
                setErrorRentabilidad(resp?.message || 'Error al cargar la rentabilidad.');
            }
        } catch (e) {
            console.error(e);
            setErrorRentabilidad('Error de conexión al cargar rentabilidad.');
        } finally {
            setCargandoRentabilidad(false);
        }
    };

    const cerrarRentabilidad = () => {
        setMostrarRentabilidad(false);
        setProductoSeleccionado(null);
        setDetalleRentabilidad(null);
        setErrorRentabilidad(null);
    };

    // Filtramos los productos basándonos en el término de búsqueda
    const productosFiltrados = productos.filter(p =>
        p.nombre.toLowerCase().includes(busqueda.toLowerCase())
    );

    // --- 4. Renderizado condicional ---
    if (isLoading) {
        return <Spinner />;
    }
    if (error) {
        return <div className="error-message">{error}</div>;
    }

    return (
        <div className="app-container">
            <div className="productos-container">
                <h1 className="productos-title">Productos Creados</h1>
                <p className="productos-description">
                    Lista de todos los productos y platos disponibles en tu inventario.
                </p>

                <Input
                    className="productos-buscador"
                    placeholder="Buscar producto por nombre..."
                    value={busqueda}
                    onChange={(e) => setBusqueda(e.target.value)}
                />
                <div className="productos-table-wrapper">
                    <table className="productos-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {productosFiltrados.map(producto => (
                                <tr key={producto.id}>
                                    <td>{producto.nombre}</td>
                                    <td>{producto.descripcion}</td>
                                    <td>{producto.categoria}</td>
                                    <td>${parseFloat(producto.precio).toFixed(2)}</td>
                                    <td>
                                        <Button
                                            onClick={() => handleDelete(producto.id)}
                                            variant="danger"
                                            className="btn-eliminar"
                                        >
                                            Eliminar
                                        </Button>
                                        <Button
                                            onClick={() => window.location.href = `/productos/${producto.id}/editar-ingredientes`}
                                            variant="secondary"
                                            className="btn-editar"
                                            style={{ marginLeft: '8px' }}
                                        >
                                            Editar Ingredientes
                                        </Button>
                                        <Button
                                            onClick={() => handleVerRentabilidad(producto)}
                                            variant="primary"
                                            className="btn-rentabilidad"
                                            style={{ marginLeft: '8px' }}
                                        >
                                            Ver Rentabilidad
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                {mostrarRentabilidad && (
                    <div className="rentabilidad-modal-overlay">
                        <div className="rentabilidad-modal">
                            <div className="rentabilidad-modal-header">
                                <h2>Rentabilidad del Producto</h2>
                                <button className="rentabilidad-close" onClick={cerrarRentabilidad}>×</button>
                            </div>
                            <div className="rentabilidad-modal-body">
                                {productoSeleccionado && (
                                    <div className="rentabilidad-resumen">
                                        <h3>{productoSeleccionado.nombre}</h3>
                                        <div className="rentabilidad-grid">
                                            <div><strong>Precio de Venta:</strong> ${parseFloat(productoSeleccionado.precio || 0).toFixed(2)}</div>
                                            <div><strong>Categoría:</strong> {productoSeleccionado.categoria || 'N/A'}</div>
                                        </div>
                                    </div>
                                )}

                                {cargandoRentabilidad && (
                                    <div style={{ textAlign: 'center', padding: '16px' }}>
                                        <Spinner />
                                        <div style={{ marginTop: '8px' }}>Cargando rentabilidad...</div>
                                    </div>
                                )}

                                {errorRentabilidad && (
                                    <div className="error-message" style={{ marginTop: '8px' }}>{errorRentabilidad}</div>
                                )}

                                {!cargandoRentabilidad && !errorRentabilidad && detalleRentabilidad && (
                                    <div className="rentabilidad-detalle">
                                        <div className="rentabilidad-grid" style={{ marginBottom: '12px' }}>
                                            <div><strong>Costo de Fabricación:</strong> ${parseFloat(detalleRentabilidad.costo_fabricacion || 0).toFixed(2)}</div>
                                            <div><strong>Ganancia Estimada:</strong> ${(
                                                parseFloat(productoSeleccionado?.precio || 0) - parseFloat(detalleRentabilidad?.costo_fabricacion || 0)
                                            ).toFixed(2)}</div>
                                        </div>
                                        <h4>Ingredientes</h4>
                                        {Array.isArray(detalleRentabilidad.ingredientes) && detalleRentabilidad.ingredientes.length > 0 ? (
                                            <table className="productos-table" style={{ marginTop: '8px' }}>
                                                <thead>
                                                    <tr>
                                                        <th>Ingrediente</th>
                                                        <th>Cantidad</th>
                                                        <th>Unidad</th>
                                                        <th>Costo Unitario</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {detalleRentabilidad.ingredientes.map((ing, idx) => (
                                                        <tr key={idx}>
                                                            <td>{ing.ingrediente_nombre}</td>
                                                            <td>{ing.cantidad}</td>
                                                            <td>{ing.unidad_medida}</td>
                                                            <td>${parseFloat(ing.costo_unitario || 0).toFixed(2)}</td>
                                                            <td>${parseFloat(ing.costo_subtotal || 0).toFixed(2)}</td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        ) : (
                                            <div className="text-muted">Este producto no tiene ingredientes definidos o no se pudieron cargar.</div>
                                        )}

                                        <div className="rentabilidad-descripcion" style={{ marginTop: '12px' }}>
                                            <p>
                                                Descripción: La ganancia se calcula comparando el costo total de ingredientes del plato con su precio de venta. Si la ganancia es negativa, el producto está generando pérdidas.
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ProductosCreados;