// --- Componente para crear nuevos productos ---
// Este archivo gestiona el formulario y la lógica para agregar productos al sistema.
import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import productoService from '@/features/productos/services/productoService';
import SelectorIngredientes from '@/features/productos/components/SelectorIngredientes';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/productos.css'; // Un CSS dedicado para los formularios de productos

// --- Estado inicial del formulario ---
// Define los valores por defecto para cada campo del producto.
const estadoInicial = {
    nombre: '',
    descripcion: '',
    precio: '',
    categoria_id: '', // Cambiado a categoria_id para coincidir con el backend
    cantidad: 1, // Campo añadido para la cantidad
    ingredientes: [], // Array para los IDs de ingredientes
};

// --- Componente principal ---
// Muestra el formulario, gestiona los datos y envía la información al backend.
function ProductoNuevo() {
    const [formData, setFormData] = useState(estadoInicial);
    const [categorias, setCategorias] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    // Cargar las categorías al montar el componente
    // --- Cargar categorías al montar el componente ---
    // Obtiene la lista de categorías desde el backend para mostrar en el select.
    useEffect(() => {
        const cargarDatos = async () => {
            try {
                const categoriasData = await productoService.getCategorias();
                setCategorias(categoriasData);

                if (categoriasData.length > 0) {
                    // Establecemos una categoría por defecto
                    setFormData(prev => ({ ...prev, categoria_id: categoriasData[0].id }));
                }
            } catch (err) {
                setError('No se pudieron cargar las categorías.');
            }
        };
        cargarDatos();
    }, []);

    // --- Manejar cambios en los inputs ---
    // Actualiza el estado del formulario cada vez que el usuario escribe.
    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    // --- Manejar selección de ingredientes ---
    // Actualiza el estado cuando el usuario selecciona ingredientes.
    const handleIngredientesChange = (ingredientesSeleccionados) => {
        setFormData(prev => ({ ...prev, ingredientes: ingredientesSeleccionados }));
    };

    // --- Enviar el formulario ---
    // Envía los datos al backend y muestra un mensaje de éxito o error.
    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError('');

        try {
            // Mapeamos los ingredientes para que tengan la propiedad 'ingrediente_id'
            const ingredientesFormateados = (formData.ingredientes || []).map(ing => ({
                ingrediente_id: ing.id,
                cantidad: ing.cantidad
            }));

            // Creamos el objeto de datos para enviar
            const datosEnviar = {
                ...formData,
                ingredientes: ingredientesFormateados
            };

            await productoService.createProducto(datosEnviar);
            alert('Producto creado con éxito.');
            navigate('/productos/creados'); // Redirige a la lista de productos
        } catch (err) {
            setError(err.response?.data?.error || 'Error al crear el producto.');
        } finally {
            setIsLoading(false);
        }
    };

    // --- Renderizado del formulario ---
    // Muestra todos los campos y botones para crear el producto.
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
                        <label htmlFor="categoria_id">Categoría</label>
                        <select
                            id="categoria_id"
                            name="categoria_id"
                            value={formData.categoria_id}
                            onChange={handleInputChange}
                            className="form-input"
                        >
                            {categorias.map(cat => (
                                <option key={cat.id} value={cat.id}>{cat.nombre}</option>
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

                    <Input
                        label="Cantidad en inventario"
                        id="cantidad"
                        name="cantidad"
                        type="number"
                        value={formData.cantidad}
                        onChange={handleInputChange}
                        required
                        min="0"
                    />

                    <SelectorIngredientes
                        ingredientesSeleccionados={formData.ingredientes}
                        onIngredientesChange={handleIngredientesChange}
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