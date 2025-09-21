import React, { useState } from 'react';
import { createMesa } from '../services/mesaService';
import Button from '@/components/Button';

function MesaCrear({ onSuccess }) {
  const [nombre, setNombre] = useState('');
  const [estado, setEstado] = useState('libre');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await createMesa({ nombre, estado });
      setNombre('');
      setEstado('libre');
      if (onSuccess) onSuccess();
    } catch {
      setError('Error al crear la mesa');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Crear Mesa</h2>
      <input
        type="text"
        value={nombre}
        onChange={e => setNombre(e.target.value)}
        placeholder="Nombre de la mesa"
        required
      />
      <select value={estado} onChange={e => setEstado(e.target.value)}>
        <option value="libre">Libre</option>
        <option value="ocupada">Ocupada</option>
        <option value="reservada">Reservada</option>
      </select>
      <Button type="submit" disabled={loading}>Crear</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default MesaCrear;
