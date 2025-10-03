import React, { useEffect, useState } from 'react';
import { getCategorias, deleteCategoria } from '@/features/categorias/services/categoriaService';
import Button from '@/components/Button';

function CategoriasLista({ onEdit }) {
  const [categorias, setCategorias] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    getCategorias()
      .then(setCategorias)
      .catch(() => setError('Error al cargar categorías'))
      .finally(() => setLoading(false));
  }, []);

  const handleDelete = async (id) => {
    if (window.confirm('¿Eliminar esta categoría?')) {
      await deleteCategoria(id);
      setCategorias(categorias.filter(cat => cat.id !== id));
    }
  };

  if (loading) return <div>Cargando categorías...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Categorías</h2>
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {categorias.map(cat => (
            <tr key={cat.id}>
              <td>{cat.nombre}</td>
              <td>
                <Button onClick={() => onEdit(cat.id)} variant="secondary">Editar</Button>
                <Button onClick={() => handleDelete(cat.id)} variant="danger" style={{marginLeft:8}}>Eliminar</Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default CategoriasLista;
