import React, { useEffect, useState } from 'react';
import proveedorService from '../services/proveedorService';
import Button from '@/components/Button';
import ProveedorIngredientes from './ProveedorIngredientes';
import ProveedorForm from './ProveedorForm';
import '@/styles/proveedores.css';

function ProveedoresLista({ onEdit }) {
  const [proveedores, setProveedores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [mostrarIngredientesProveedorId, setMostrarIngredientesProveedorId] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modoPedido, setModoPedido] = useState(false);
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editProveedorId, setEditProveedorId] = useState(null);

  const reloadProveedores = () => {
    proveedorService.getProveedores()
      .then(setProveedores)
      .catch(() => setError('Error al cargar proveedores'))
      .finally(() => setLoading(false));
  };

  const openNuevo = () => {
    setEditProveedorId(null);
    setIsFormOpen(true);
  };

  const openEditar = (id) => {
    setEditProveedorId(id);
    setIsFormOpen(true);
  };

  useEffect(() => {
    proveedorService.getProveedores()
      .then(setProveedores)
      .catch(() => setError('Error al cargar proveedores'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Cargando proveedores...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div className="proveedores-container">
      <div className="proveedores-header">
        <h2>Proveedores</h2>
        <Button variant="primary" onClick={openNuevo}>Nuevo Proveedor</Button>
      </div>
      <div className="table-responsive">
      <table className="proveedores-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Contacto</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {proveedores.map(prov => (
            <tr key={prov.id}>
              <td data-label="Nombre">{prov.nombre}</td>
              <td data-label="Contacto">{prov.contacto}</td>
              <td data-label="Teléfono">{prov.telefono}</td>
              <td data-label="Email">{prov.email}</td>
              <td className="actions" data-label="Acciones">
                <Button onClick={() => (onEdit ? onEdit(prov.id) : openEditar(prov.id))} variant="secondary">Editar</Button>
                <Button onClick={() => { setModoPedido(false); setMostrarIngredientesProveedorId(prov.id); setIsModalOpen(true); }} variant="info">Ver Ingredientes</Button>
                <Button
                  onClick={() => { setModoPedido(true); setMostrarIngredientesProveedorId(prov.id); setIsModalOpen(true); }}
                  variant="success"
                >
                  Pedir por WhatsApp
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      </div>
      {isModalOpen && mostrarIngredientesProveedorId && (
        <div className="modal-overlay"
          onClick={() => { setIsModalOpen(false); setMostrarIngredientesProveedorId(null); }}
        >
          <div className="modal-content"
            onClick={(e) => e.stopPropagation()}
          >
            <h3>Ingredientes del Proveedor</h3>
            <ProveedorIngredientes proveedorId={mostrarIngredientesProveedorId} modoPedido={modoPedido} />
            <div style={{ marginTop: '12px', display: 'flex', justifyContent: 'flex-end' }}>
              <Button variant="secondary" onClick={() => { setIsModalOpen(false); setMostrarIngredientesProveedorId(null); }}>
                Cerrar
              </Button>
            </div>
          </div>
        </div>
      )}
      {isFormOpen && (
        <div className="modal-overlay" onClick={() => setIsFormOpen(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <h3>{editProveedorId ? 'Editar Proveedor' : 'Nuevo Proveedor'}</h3>
            <ProveedorForm proveedorId={editProveedorId} onSaved={() => { setIsFormOpen(false); reloadProveedores(); }} />
            <div style={{ marginTop: '12px', display: 'flex', justifyContent: 'flex-end' }}>
              <Button variant="secondary" onClick={() => setIsFormOpen(false)}>Cerrar</Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default ProveedoresLista;
