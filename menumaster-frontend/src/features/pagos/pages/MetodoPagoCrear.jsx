import React, { useState } from 'react';
import { createMetodoPago } from '../services/pagoService';
import Button from '@/components/Button';

function MetodoPagoCrear({ onSuccess }) {
  const [nombre, setNombre] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await createMetodoPago({ nombre });
      setNombre('');
      if (onSuccess) onSuccess();
    } catch {
      setError('Error al crear método de pago');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Crear Método de Pago</h2>
      <input
        type="text"
        value={nombre}
        onChange={e => setNombre(e.target.value)}
        placeholder="Nombre del método"
        required
      />
      <Button type="submit" disabled={loading}>Crear</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default MetodoPagoCrear;
