import React, { useEffect, useState } from 'react';
import proveedorService from '../services/proveedorService';
import Button from '@/components/Button';

function ProveedoresLista({ onEdit }) {
  const [proveedores, setProveedores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    proveedorService.getProveedores()
      .then(setProveedores)
      .catch(() => setError('Error al cargar proveedores'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Cargando proveedores...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Proveedores</h2>
      <table>
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
              <td>{prov.nombre}</td>
              <td>{prov.contacto}</td>
              <td>{prov.telefono}</td>
              <td>{prov.email}</td>
              <td>
                <Button onClick={() => onEdit(prov.id)} variant="secondary">Editar</Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default ProveedoresLista;
