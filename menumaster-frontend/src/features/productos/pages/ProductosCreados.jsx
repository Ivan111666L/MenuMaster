import React, { useState, useEffect } from 'react';
import productoService from '@/features/productos/services/productoService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/productos.css'; // Estilos dedicados para esta página

function ProductosCreados() {
    const [productos, setProductos] = useState([]);
    const [busqueda, setBusqueda] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // Función para cargar los productos desde la API
    const cargarProductos = async () => {
        try {
            setIsLoading(true);
            const data = await productoService.getProductos();
            setProductos(Array.isArray(data) ? data : []);
        } catch (err) {
            setError('No se pudieron cargar los productos.');
        } finally {
            setIsLoading(false);
        }
    };

    // Cargar los datos cuando el componente se monta
    useEffect(() => {
        cargarProductos();
    }, []);

    const handleDelete = async (id) => {
        if (window.confirm('¿Estás seguro de que quieres eliminar este producto?')) {
            try {
                await productoService.deleteProducto(id);
                // Si se elimina con éxito, filtramos la lista localmente para una UI más rápida
                setProductos(prevProductos => prevProductos.filter(p => p.id !== id));
            } catch (err) {
                alert('Error al eliminar el producto.');
            }
        }
    };

    // Filtramos los productos basándonos en el término de búsqueda
    const productosFiltrados = productos.filter(p =>
        p.nombre.toLowerCase().includes(busqueda.toLowerCase())
    );

    if (isLoading) return <Spinner />;
    if (error) return <div className="error-message">{error}</div>;

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
                        <thead className="table-header">
                            <tr>
                                <th className="table-header-cell">Producto</th>
                                <th className="table-header-cell">Descripción</th>
                                <th className="table-header-cell">Categoría</th>
                                <th className="table-header-cell">Precio</th>
                                <th className="table-header-cell">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="table-body">
                            {productosFiltrados.map(producto => (
                                <tr key={producto.id} className="table-row">
                                    <td className="table-cell font-semibold">{producto.nombre}</td>
                                    <td className="table-cell">{producto.descripcion}</td>
                                    <td className="table-cell">{producto.categoria}</td>
                                    <td className="table-cell">${parseFloat(producto.precio).toFixed(2)}</td>
                                    <td className="table-cell">
                                        <Button
                                            onClick={() => handleDelete(producto.id)}
                                            variant="danger"
                                            className="btn-eliminar"
                                        >
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default ProductosCreados;