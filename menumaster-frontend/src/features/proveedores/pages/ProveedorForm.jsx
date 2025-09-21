import React, { useState, useEffect } from 'react';
import proveedorService from '../services/proveedorService';
import Button from '@/components/Button';

function ProveedorForm({ proveedorId, onSaved }) {
  const [form, setForm] = useState({ nombre: '', contacto: '', telefono: '', email: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (proveedorId) {
      setLoading(true);
      proveedorService.getProveedorById(proveedorId)
        .then(data => setForm(data))
        .catch(() => setError('Error al cargar proveedor'))
        .finally(() => setLoading(false));
    }
  }, [proveedorId]);

  const handleChange = e => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      if (proveedorId) {
        await proveedorService.updateProveedor(proveedorId, form);
      } else {
        await proveedorService.createProveedor(form);
      }
      onSaved();
    } catch {
      setError('Error al guardar proveedor');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>{proveedorId ? 'Editar Proveedor' : 'Nuevo Proveedor'}</h2>
      <label>Nombre:<input name="nombre" value={form.nombre} onChange={handleChange} required /></label>
      <label>Contacto:<input name="contacto" value={form.contacto} onChange={handleChange} /></label>
      <label>Teléfono:<input name="telefono" value={form.telefono} onChange={handleChange} /></label>
      <label>Email:<input name="email" value={form.email} onChange={handleChange} /></label>
      {error && <div>{error}</div>}
      <Button type="submit" disabled={loading}>{loading ? 'Guardando...' : 'Guardar'}</Button>
    </form>
  );
}

export default ProveedorForm;
