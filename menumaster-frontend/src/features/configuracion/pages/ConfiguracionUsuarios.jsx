import React, { useState } from 'react';
import { useUsuarios } from '@/features/configuracion/hooks/useUsuarios'; // Importamos el nuevo hook
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/configuracion.css'; // Asegúrate de tener este archivo de estilos

function ConfiguracionUsuarios() {
  // Toda la lógica y el estado ahora viven dentro del hook.
  const { usuarios, isLoading, error, updateUsuario, deleteUsuario } = useUsuarios();
  const [busqueda, setBusqueda] = useState('');

  const usuariosFiltrados = usuarios.filter(u =>
    `${u.nombre} ${u.email}`.toLowerCase().includes(busqueda.toLowerCase())
  );
  
  if (isLoading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="gestion-usuarios-container">
      <h1 className="gestion-usuarios-title">Gestión de Usuarios</h1>
      <Input
        placeholder="Buscar por nombre o correo..."
        value={busqueda}
        onChange={(e) => setBusqueda(e.target.value)}
        className="buscador-usuarios"
      />
      <table className="usuarios-table">
        {/* ... (El JSX de tu tabla permanece igual, pero ahora usa las funciones 'updateUsuario' y 'deleteUsuario' del hook) ... */}
        <tbody>
          {usuariosFiltrados.map((u) => (
            <tr key={u.id}>
              <td>{u.nombre}</td>
              <td>{u.email}</td>
              <td>
                <select 
                  value={u.rol} 
                  onChange={(e) => updateUsuario(u.id, { rol: e.target.value })}
                >
                  <option value="administrador">Administrador</option>
                  <option value="mesero">Mesero</option>
                  <option value="cocinero">Cocinero</option>
                  <option value="cajero">Cajero</option>
                </select>
              </td>
              <td>
                <Button
                  onClick={() => updateUsuario(u.id, { estado: u.estado === 'activo' ? 'inactivo' : 'activo' })}
                  variant={u.estado === 'activo' ? 'primary' : 'secondary'}
                >
                  {u.estado}
                </Button>
              </td>
              <td>
                <Button variant="danger" onClick={() => deleteUsuario(u.id)}>
                  Eliminar
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default ConfiguracionUsuarios;
