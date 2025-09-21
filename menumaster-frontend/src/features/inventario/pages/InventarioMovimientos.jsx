import React, { useEffect, useState } from 'react';
import { getMovimientos, createMovimiento } from '../services/inventarioService';
import Button from '@/components/Button';

function InventarioMovimientos() {
  const [movimientos, setMovimientos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [form, setForm] = useState({ producto_id: '', cantidad: '', tipo: 'entrada' });
  const [sending, setSending] = useState(false);

  useEffect(() => {
    getMovimientos()
      .then(setMovimientos)
      .catch(() => setError('Error al cargar movimientos'))
      .finally(() => setLoading(false));
  }, []);

  const handleChange = e => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setSending(true);
    setError('');
    try {
      await createMovimiento(form);
      setForm({ producto_id: '', cantidad: '', tipo: 'entrada' });
      const updated = await getMovimientos();
      setMovimientos(updated);
    } catch {
      setError('Error al registrar movimiento');
    } finally {
      setSending(false);
    }
  };

  if (loading) return <div>Cargando movimientos...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Movimientos de Inventario</h2>
      <form onSubmit={handleSubmit} style={{marginBottom:16}}>
        <input
          name="producto_id"
          value={form.producto_id}
          onChange={handleChange}
          placeholder="ID Producto"
          required
        />
        <input
          name="cantidad"
          type="number"
          value={form.cantidad}
          onChange={handleChange}
          placeholder="Cantidad"
          required
        />
        <select name="tipo" value={form.tipo} onChange={handleChange}>
          <option value="entrada">Entrada</option>
          <option value="salida">Salida</option>
        </select>
        <Button type="submit" disabled={sending}>Registrar</Button>
      </form>
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Tipo</th>
            <th>Usuario</th>
          </tr>
        </thead>
        <tbody>
          {movimientos.map(mov => (
            <tr key={mov.id}>
              <td>{mov.fecha}</td>
              <td>{mov.producto_nombre}</td>
              <td>{mov.cantidad}</td>
              <td>{mov.tipo}</td>
              <td>{mov.usuario_nombre}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default InventarioMovimientos;
