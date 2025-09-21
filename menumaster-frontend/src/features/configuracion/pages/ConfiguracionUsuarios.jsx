import { useState } from 'react';
import useUsuarios from '@/features/configuracion/hooks/useUsuarios';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
<<<<<<< HEAD
import '@/styles/configuracion.css'; // Asegúrate de tener este archivo de estilos
=======
import '@/styles/configuracion.css';
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a

function ConfiguracionUsuarios() {
  const { usuarios, isLoading, error, updateUsuario, deleteUsuario } = useUsuarios();
  const [busqueda, setBusqueda] = useState('');
  const usuariosFiltrados = usuarios ? usuarios.filter(usuario =>
    `${usuario.nombre} ${usuario.email}`.toLowerCase().includes(busqueda.toLowerCase())
  ) : [];

  if (isLoading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="gestion-usuarios-container">
      <h1 className="gestion-usuarios-title">Gestión de Usuarios</h1>
      
      <div className="acciones-container">
        <Input
          placeholder="Buscar por nombre o correo..."
          value={busqueda}
          onChange={(e) => setBusqueda(e.target.value)}
          className="buscador-usuarios"
        />
      </div>
      
      <div className="tabla-container">
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
            {usuariosFiltrados.length === 0 ? (
              <tr>
                <td colSpan="5" className="text-center">No se encontraron usuarios</td>
              </tr>
            ) : (
              usuariosFiltrados.map((usuario) => (
                <tr key={usuario.id}>
                  <td>{usuario.nombre}</td>
                  <td>{usuario.email}</td>
                  <td>
                    <select
                      value={usuario.rol || ''}
                      onChange={(e) => updateUsuario(usuario.id, { rol: e.target.value })}
                      className="form-control"
                    >
                      <option value="">Seleccionar rol</option>
                      <option value="administrador">Administrador</option>
                      <option value="mesero">Mesero</option>
                      <option value="cocinero">Cocinero</option>
                      <option value="cajero">Cajero</option>
                    </select>
                  </td>
                  <td>
                    <Button
                      onClick={() => updateUsuario(usuario.id, { estado: usuario.estado === 'activo' ? 'inactivo' : 'activo' })}
                      variant={usuario.estado === 'activo' ? 'primary' : 'secondary'}
                      className="estado-btn"
                    >
                      {usuario.estado === 'activo' ? 'Activo' : 'Inactivo'}
                    </Button>
                  </td>
                  <td className="acciones">
                    <Button
                      onClick={() => {
                        if (window.confirm('¿Estás seguro de eliminar este usuario?')) {
                          deleteUsuario(usuario.id);
                        }
                      }}
                      variant="danger"
                    >
                      Eliminar
                    </Button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default ConfiguracionUsuarios;

