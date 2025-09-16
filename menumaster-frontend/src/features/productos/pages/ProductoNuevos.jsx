import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import productoService from '@/features/productos/services/productoService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/productos.css'; // Un CSS dedicado para los formularios de productos

const estadoInicial = {
    nombre: '',
    descripcion: '',
    precio: '',
    categoria_nombre: '', // Usaremos el nombre para enviarlo al backend
    // Los campos de stock podrían manejarse en la sección de inventario, pero los dejamos aquí por ahora.
};

function ProductoNuevo() {
    const [formData, setFormData] = useState(estadoInicial);
    const [categorias, setCategorias] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    // Cargar las categorías al montar el componente
    useEffect(() => {
        const cargarCategorias = async () => {
            try {
                const data = await productoService.getCategorias();
                setCategorias(data);
                if (data.length > 0) {
                    // Establecemos una categoría por defecto
                    setFormData(prev => ({ ...prev, categoria_nombre: data[0].nombre }));
                }
            } catch (err) {
                setError('No se pudieron cargar las categorías.');
            }
        };
        cargarCategorias();
    }, []);

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError('');

        try {
            // El backend espera 'estado_nombre', lo añadimos por defecto.
            const dataToSend = { ...formData, estado_nombre: 'disponible' };
            await productoService.createProducto(dataToSend);
            alert('Producto creado con éxito.');
            navigate('/productos/creados'); // Redirige a la lista de productos
        } catch (err) {
            setError(err.response?.data?.error || 'Error al crear el producto.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="app-container">
            <div className="productos-form-wrapper">
                <h1 className="productos-title">Crear Nuevo Producto</h1>
                <p className="productos-description">
                    Completa el formulario para agregar un nuevo plato, bebida o postre a tu menú.
                </p>
                <form onSubmit={handleSubmit} className="productos-form">
                    <Input
                        label="Nombre del Producto"
                        id="nombre"
                        name="nombre"
                        value={formData.nombre}
                        onChange={handleInputChange}
                        required
                    />
                    
                    <div className="form-group">
                        <label htmlFor="descripcion">Descripción</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="3"
                            value={formData.descripcion}
                            onChange={handleInputChange}
                            className="form-input"
                        ></textarea>
                    </div>

                    <div className="form-group">
                        <label htmlFor="categoria_nombre">Categoría</label>
                        <select
                            id="categoria_nombre"
                            name="categoria_nombre"
                            value={formData.categoria_nombre}
                            onChange={handleInputChange}
                            className="form-input"
                        >
                            {categorias.map(cat => (
                                <option key={cat.id} value={cat.nombre}>{cat.nombre}</option>
                            ))}
                        </select>
                    </div>
                    
                    <Input
                        label="Precio"
                        id="precio"
                        name="precio"
                        type="number"
                        value={formData.precio}
                        onChange={handleInputChange}
                        required
                        step="0.01"
                    />
                    
                    {error && <p className="auth-error-message">{error}</p>}

                    <div className="form-actions">
                        <Button type="submit" variant="primary" disabled={isLoading}>
                            {isLoading ? <Spinner /> : 'Guardar Producto'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default ProductoNuevo;