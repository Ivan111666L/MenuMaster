import React, { useState, useEffect } from 'react';
import productoService from '@/features/productos/services/productoService'; // Nuestro nuevo servicio
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