import React, { useState } from 'react';
import { createCategoria } from '../services/categoriaService';
import Button from '@/components/Button';

function CategoriaCrear({ onSuccess }) {
  const [nombre, setNombre] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await createCategoria({ nombre });
      setNombre('');
      if (onSuccess) onSuccess();
    } catch {
      setError('Error al crear la categoría');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Crear Categoría</h2>
      <input
        type="text"
        value={nombre}
        onChange={e => setNombre(e.target.value)}
        placeholder="Nombre de la categoría"
        required
      />
      <Button type="submit" disabled={loading}>Crear</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default CategoriaCrear;
