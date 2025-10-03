import React from 'react';
import { Link, Outlet } from 'react-router-dom';

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

       
      </div>
      {/* Renderiza rutas hijas directamente cuando se visita /configuracion/usuarios o /configuracion/mesas */}
      <Outlet />
    </div>
  );
};

export default Configuracion;