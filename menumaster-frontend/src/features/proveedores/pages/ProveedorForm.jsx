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
        .then(data => setForm({
          nombre: data?.nombre || '',
          contacto: data?.contacto || '',
          telefono: data?.telefono || '',
          email: data?.email || ''
        }))
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
      const payload = {
        nombre: form.nombre,
        contacto: form.contacto,
        telefono: form.telefono,
        email: form.email
      };
      if (proveedorId) {
        await proveedorService.updateProveedor(proveedorId, payload);
      } else {
        await proveedorService.createProveedor(payload);
      }
      onSaved();
    } catch {
      setError('Error al guardar proveedor');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="form-container">
      <h2>{proveedorId ? 'Editar Proveedor' : 'Nuevo Proveedor'}</h2>
      <div className="form-grid">
        <div className="form-group">
          <label htmlFor="nombre" className="form-label">Nombre</label>
          <input id="nombre" name="nombre" value={form.nombre} onChange={handleChange} required className="form-input" />
        </div>
        <div className="form-group">
          <label htmlFor="contacto" className="form-label">Contacto</label>
          <input id="contacto" name="contacto" value={form.contacto} onChange={handleChange} className="form-input" />
        </div>
        <div className="form-group">
          <label htmlFor="telefono" className="form-label">Teléfono</label>
          <input id="telefono" name="telefono" value={form.telefono} onChange={handleChange} className="form-input" />
        </div>
        <div className="form-group">
          <label htmlFor="email" className="form-label">Email</label>
          <input id="email" name="email" value={form.email} onChange={handleChange} className="form-input" />
        </div>
      </div>
      {error && <div className="form-input-error">{error}</div>}
      <div className="form-actions">
        <Button type="submit" disabled={loading}>{loading ? 'Guardando...' : 'Guardar'}</Button>
      </div>
    </form>
  );
}

export default ProveedorForm;
