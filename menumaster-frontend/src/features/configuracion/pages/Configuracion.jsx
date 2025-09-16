import React from 'react';
import { Link } from 'react-router-dom';

// Importamos el componente reutilizable de Botón
import Button from '@/components/Button';
// Importamos los nuevos estilos
import '@/styles/configuracion.css';

function Configuracion() {
  return (
    <div className="configuracion-container">
      <h1 className="configuracion-title">Panel de Configuración</h1>

      <div className="card-container">
        <div className="card">
          <h3 className="card-title">Gestión de Usuarios</h3>
          <p>Administra usuarios registrados: roles, estado, y eliminación.</p>
          {/* CORRECCIÓN: Usamos Link para la navegación y Button para el estilo */}
          <Link to="/configuracion/usuarios" className="card-link">
            <Button variant="primary">Ir a Usuarios</Button>
          </Link>
        </div>

        <div className="card">
          <h3 className="card-title">Manejo de Mesas</h3>
          <p>Administra las mesas disponibles: estado, reservas, y ubicación.</p>
          <Link to="/configuracion/mesas" className="card-link">
            <Button variant="primary">Ir a Mesas</Button>
          </Link>
        </div>

        <div className="card">
          <h3 className="card-title">Configuración de Roles</h3>
          <p>Define permisos y accesos según el rol del usuario.</p>
          <Link to="/configuracion/roles" className="card-link">
            <Button variant="primary">Ir a Roles</Button>
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Configuracion;