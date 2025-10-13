import React, { useEffect, useState } from 'react';
import { createPago, getMetodosPago } from '../services/pagoService';
import Button from '@/components/Button';

function PagoCrear({ onSuccess, pedidoId = null, montoSugerido = '' }) {
  const [monto, setMonto] = useState(String(montoSugerido ?? ''));
  const [metodoId, setMetodoId] = useState('');
  const [metodos, setMetodos] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getMetodosPago().then(setMetodos);
  }, []);

  // Actualizar el monto sugerido al cambiar el pedido seleccionado
  useEffect(() => {
    setMonto(String(montoSugerido ?? ''));
  }, [montoSugerido, pedidoId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const payload = { monto, metodo_id: metodoId };
      if (pedidoId) payload.pedido_id = pedidoId;
      await createPago(payload);
      // Limpiar campos tras registrar
      setMetodoId('');
      if (!pedidoId) setMonto(''); // si está ligado al pedido, mantener el monto sugerido
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
      {pedidoId && (
        <p style={{ marginTop: 0, color: '#6B7280' }}>
          Pedido seleccionado: #{pedidoId}
        </p>
      )}
      <label htmlFor="pago-monto">Monto</label>
      <input
        id="pago-monto"
        name="monto"
        type="number"
        value={monto}
        onChange={e => setMonto(e.target.value)}
        placeholder="Monto"
        required
        inputMode="decimal"
        autoComplete="transaction-amount"
      />
      <label htmlFor="pago-metodo">Método de pago</label>
      <select
        id="pago-metodo"
        name="metodo_id"
        value={metodoId}
        onChange={e => setMetodoId(e.target.value)}
        required
        autoComplete="cc-type"
      >
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
