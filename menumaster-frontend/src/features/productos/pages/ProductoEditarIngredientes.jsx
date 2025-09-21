import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import productoService from '@/features/productos/services/productoService';
import SelectorIngredientes from '@/features/productos/components/SelectorIngredientes';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/productos.css';

function ProductoEditarIngredientes() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [producto, setProducto] = useState(null);
  const [ingredientes, setIngredientes] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const cargarProducto = async () => {
      try {
        const data = await productoService.getProductoById(id);
        setProducto(data);
        setIngredientes(data.ingredientes || []);
      } catch (err) {
        setError('No se pudo cargar el producto.');
      } finally {
        setIsLoading(false);
      }
    };
    cargarProducto();
  }, [id]);

  const handleIngredientesChange = (ingredientesSeleccionados) => {
    setIngredientes(ingredientesSeleccionados);
  };

  const handleGuardar = async () => {
    setIsLoading(true);
    setError('');
    try {
      await productoService.updateProducto(id, { ingredientes });
      alert('Ingredientes actualizados correctamente.');
      navigate('/productos/creados');
    } catch (err) {
      setError('Error al actualizar los ingredientes.');
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="app-container">
      <div className="productos-form-wrapper">
        <h1 className="productos-title">Editar Ingredientes del Producto</h1>
        <p className="productos-description">
          Modifica los ingredientes asociados al producto: <b>{producto?.nombre}</b>
        </p>
        <SelectorIngredientes
          ingredientesSeleccionados={ingredientes}
          onIngredientesChange={handleIngredientesChange}
        />
        <div className="form-actions">
          <Button onClick={handleGuardar} variant="primary" disabled={isLoading}>
            {isLoading ? <Spinner /> : 'Guardar Ingredientes'}
          </Button>
        </div>
      </div>
    </div>
  );
}

export default ProductoEditarIngredientes;
