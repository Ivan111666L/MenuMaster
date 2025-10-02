import React, { useEffect, useState } from 'react';
import { getCategoriaById, updateCategoria } from '../services/categoriaService';
import Button from '@/components/Button';

function CategoriaEditar({ categoriaId, onSuccess }) {
  const [nombre, setNombre] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (categoriaId) {
      getCategoriaById(categoriaId)
        .then(cat => setNombre(cat.nombre))
        .catch(() => setError('Error al cargar la categoría'))
        .finally(() => setLoading(false));
    }
  }, [categoriaId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await updateCategoria(categoriaId, { nombre });
      if (onSuccess) onSuccess();
    } catch {
      setError('Error al actualizar la categoría');
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Cargando...</div>;
  if (error) return <div>{error}</div>;

  return (
    <form onSubmit={handleSubmit}>
      <h2>Editar Categoría</h2>
      <input
        type="text"
        value={nombre}
        onChange={e => setNombre(e.target.value)}
        required
      />
      <Button type="submit" disabled={loading}>Guardar</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default CategoriaEditar;
