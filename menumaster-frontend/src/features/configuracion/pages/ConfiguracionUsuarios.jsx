import React, { useEffect, useState } from 'react';
import usuarioService from '@/features/usuarios/services/usuarioService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/configuracion.css';

function ConfiguracionUsuarios() {
  const [usuarios, setUsuarios] = useState([]);
  const [busqueda, setBusqueda] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  const cargarUsuarios = async () => {
    try {
      setIsLoading(true);
      const data = await usuarioService.getUsuarios();
      setUsuarios(Array.isArray(data) ? data : []);
    } catch (err) {
      setError('Error al cargar los usuarios.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    cargarUsuarios();
  }, []);

  const handleUpdate = async (id, dataToUpdate) => {
    try {
      await usuarioService.updateUsuario(id, dataToUpdate);
      // Actualizamos el estado local para una respuesta visual instantánea
      setUsuarios(prev => prev.map(u => {
        if (u.id === id) {
          return { ...u, ...dataToUpdate };
        }
        return u;
      }));
    } catch (err) {
      alert('Error al actualizar el usuario.');
      cargarUsuarios(); // Recargamos por si la UI se desincroniza
    }
  };

  const eliminarUsuario = async (id) => {
    if (window.confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
      try {
        await usuarioService.deleteUsuario(id);
        setUsuarios(prev => prev.filter(u => u.id !== id));
      } catch (err) {
        alert('Error al eliminar el usuario.');
      }
    }
  };

  const usuariosFiltrados = usuarios.filter(u =>
    `${u.nombre} ${u.email}`.toLowerCase().includes(busqueda.toLowerCase())
  );
  
  if (isLoading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="gestion-usuarios-container">
      <h1 className="gestion-usuarios-title">Gestión de Usuarios</h1>

      <Input
        placeholder="Buscar por nombre o correo electrónico..."
        value={busqueda}
        onChange={(e) => setBusqueda(e.target.value)}
        className="buscador-usuarios"
      />

      <div className="table-wrapper">
        <table className="usuarios-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {usuariosFiltrados.map((u) => (
              <tr key={u.id}>
                <td>{u.nombre}</td>
                <td>{u.email}</td>
                <td>
                  <select 
                    value={u.rol} 
                    onChange={(e) => handleUpdate(u.id, { rol: e.target.value })}
                  >
                    <option value="administrador">Administrador</option>
                    <option value="mesero">Mesero</option>
                    <option value="cocinero">Cocinero</option>
                    <option value="cajero">Cajero</option>
                  </select>
                </td>
                <td>
                  <Button
                    onClick={() => handleUpdate(u.id, { estado: u.estado === 'activo' ? 'inactivo' : 'activo' })}
                    variant={u.estado === 'activo' ? 'primary' : 'secondary'}
                  >
                    {u.estado}
                  </Button>
                </td>
                <td>
                  <Button variant="danger" onClick={() => eliminarUsuario(u.id)}>
                    Eliminar
                  </Button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default ConfiguracionUsuarios;