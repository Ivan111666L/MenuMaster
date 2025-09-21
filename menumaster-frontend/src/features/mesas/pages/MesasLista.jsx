import React, { useEffect, useState } from 'react';
import { getMesas, deleteMesa } from '../services/mesaService';
import Button from '@/components/Button';

function MesasLista({ onEdit }) {
  const [mesas, setMesas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    getMesas()
      .then(setMesas)
      .catch(() => setError('Error al cargar mesas'))
      .finally(() => setLoading(false));
  }, []);

  const handleDelete = async (id) => {
    if (window.confirm('¿Eliminar esta mesa?')) {
      await deleteMesa(id);
      setMesas(mesas.filter(mesa => mesa.id !== id));
    }
  };

  if (loading) return <div>Cargando mesas...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Mesas</h2>
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {mesas.map(mesa => (
            <tr key={mesa.id}>
              <td>{mesa.nombre}</td>
              <td>{mesa.estado}</td>
              <td>
                <Button onClick={() => onEdit(mesa.id)} variant="secondary">Editar</Button>
                <Button onClick={() => handleDelete(mesa.id)} variant="danger" style={{marginLeft:8}}>Eliminar</Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default MesasLista;
