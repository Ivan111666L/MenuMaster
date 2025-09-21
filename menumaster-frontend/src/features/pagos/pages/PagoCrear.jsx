import React, { useEffect, useState } from 'react';
import { createPago, getMetodosPago } from '../services/pagoService';
import Button from '@/components/Button';

function PagoCrear({ onSuccess }) {
  const [monto, setMonto] = useState('');
  const [metodoId, setMetodoId] = useState('');
  const [metodos, setMetodos] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getMetodosPago().then(setMetodos);
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await createPago({ monto, metodo_id: metodoId });
      setMonto('');
      setMetodoId('');
      if (onSuccess) onSuccess();
    } catch {
      setError('Error al registrar el pago');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Registrar Pago</h2>
      <input
        type="number"
        value={monto}
        onChange={e => setMonto(e.target.value)}
        placeholder="Monto"
        required
      />
      <select value={metodoId} onChange={e => setMetodoId(e.target.value)} required>
        <option value="">Selecciona método</option>
        {metodos.map(m => (
          <option key={m.id} value={m.id}>{m.nombre}</option>
        ))}
      </select>
      <Button type="submit" disabled={loading}>Registrar</Button>
      {error && <div style={{color:'red'}}>{error}</div>}
    </form>
  );
}

export default PagoCrear;
