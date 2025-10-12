import React, { useState } from 'react';
import { createMesa } from '../services/mesaService';
import { ESTADOS_MESA } from '@/utils/constant';
import Button from '@/components/Button';

function MesaCrear({ onSuccess }) {
  const [nombre, setNombre] = useState('');
  const [estado, setEstado] = useState(ESTADOS_MESA.DISPONIBLE);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      // Enviar estado como estado_nombre para alinearnos con el backend
      await createMesa({ nombre, estado_nombre: estado });
      setNombre('');
      setEstado(ESTADOS_MESA.DISPONIBLE);
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
        <option value={ESTADOS_MESA.DISPONIBLE}>Disponible</option>
        <option value={ESTADOS_MESA.OCUPADA}>Ocupada</option>
        <option value={ESTADOS_MESA.RESERVADA}>Reservada</option>
      </select>
      <Button type="submit" disabled={loading}>Crear</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default MesaCrear;
