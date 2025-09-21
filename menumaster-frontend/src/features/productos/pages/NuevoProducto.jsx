import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import productosService from '../services/productosService';
import '@/styles/productos.css';

function NuevoProducto() {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [categorias, setCategorias] = useState([]);
    const [formData, setFormData] = useState({
        nombre: '',
        descripcion: '',
        precio: '',
        categoria_id: '',
        estado_id: 1, // Asumiendo que 1 es el estado "activo"
        imagen: null
    });

    useEffect(() => {
        cargarCategorias();
    }, []);

    const cargarCategorias = async () => {
        try {
            const data = await productosService.getCategorias();
            setCategorias(data);
        } catch (error) {
            toast.error('Error al cargar las categorías');
        }
    };

    const handleInputChange = (e) => {
        const { name, value, type, files } = e.target;
        
        if (type === 'file') {
            setFormData(prev => ({
                ...prev,
                [name]: files[0]
            }));
        } else if (name === 'precio') {
            // Asegurarse de que el precio sea un número válido
            const precio = value.replace(/[^0-9.]/g, '');
            setFormData(prev => ({
                ...prev,
                [name]: precio
            }));
        } else {
            setFormData(prev => ({
                ...prev,
                [name]: value
            }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const formDataToSend = new FormData();
            Object.keys(formData).forEach(key => {
                if (formData[key] !== null) {
                    formDataToSend.append(key, formData[key]);
                }
            });

            await productosService.createProducto(formDataToSend);
            toast.success('Producto creado exitosamente');
            navigate('/productos');
        } catch (error) {
            toast.error(error.message || 'Error al crear el producto');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="producto-form-container">
            <h1>Nuevo Producto</h1>
            <form onSubmit={handleSubmit} className="producto-form">
                <div className="form-group">
                    <label htmlFor="nombre">Nombre del Producto</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value={formData.nombre}
                        onChange={handleInputChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label htmlFor="descripcion">Descripción</label>
                    <textarea
                        id="descripcion"
                        name="descripcion"
                        value={formData.descripcion}
                        onChange={handleInputChange}
                        rows="3"
                    />
                </div>

                <div className="form-group">
                    <label htmlFor="precio">Precio</label>
                    <input
                        type="text"
                        id="precio"
                        name="precio"
                        value={formData.precio}
                        onChange={handleInputChange}
                        required
                        placeholder="0.00"
                    />
                </div>

                <div className="form-group">
                    <label htmlFor="categoria_id">Categoría</label>
                    <select
                        id="categoria_id"
                        name="categoria_id"
                        value={formData.categoria_id}
                        onChange={handleInputChange}
                        required
                    >
                        <option value="">Selecciona una categoría</option>
                        {categorias.map(categoria => (
                            <option key={categoria.id} value={categoria.id}>
                                {categoria.nombre}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="form-group">
                    <label htmlFor="imagen">Imagen del Producto</label>
                    <input
                        type="file"
                        id="imagen"
                        name="imagen"
                        onChange={handleInputChange}
                        accept="image/*"
                    />
                </div>

                <div className="form-actions">
                    <button
                        type="button"
                        onClick={() => navigate('/productos')}
                        className="btn-secondary"
                        disabled={loading}
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        className="btn-primary"
                        disabled={loading}
                    >
                        {loading ? 'Guardando...' : 'Guardar Producto'}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default NuevoProducto;