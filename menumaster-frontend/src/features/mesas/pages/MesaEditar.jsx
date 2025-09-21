import React, { useEffect, useState } from 'react';
import { getMesa, updateMesa } from '../services/mesaService';
import Button from '@/components/Button';

function MesaEditar({ mesaId, onSuccess }) {
  const [nombre, setNombre] = useState('');
  const [estado, setEstado] = useState('libre');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (mesaId) {
      getMesa(mesaId)
        .then(mesa => {
          setNombre(mesa.nombre);
          setEstado(mesa.estado);
        })
        .catch(() => setError('Error al cargar la mesa'))
        .finally(() => setLoading(false));
    }
  }, [mesaId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await updateMesa(mesaId, { nombre, estado });
      if (onSuccess) onSuccess();
    } catch {
      setError('Error al actualizar la mesa');
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Cargando...</div>;
  if (error) return <div>{error}</div>;

  return (
    <form onSubmit={handleSubmit}>
      <h2>Editar Mesa</h2>
      <input
        type="text"
        value={nombre}
        onChange={e => setNombre(e.target.value)}
        required
      />
      <select value={estado} onChange={e => setEstado(e.target.value)}>
        <option value="libre">Libre</option>
        <option value="ocupada">Ocupada</option>
        <option value="reservada">Reservada</option>
      </select>
      <Button type="submit" disabled={loading}>Guardar</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default MesaEditar;
