import React from 'react';
import { Link } from 'react-router-dom';
import '@/styles/global.css';

/**
 * Esta página se muestra cuando un usuario autenticado no tiene los permisos
 * necesarios para acceder a una ruta específica.
 */
function Unauthorized() {
  return (
    <div className="error-container">
      <div className="error-content">
        <h1 className="error-code">403</h1>
        <h2 className="error-title">Acceso Denegado</h2>
        <p className="error-message">
          Lo sentimos, no tienes los permisos necesarios para ver esta página.
        </p>
        <Link to="/" className="home-link-button">
          Volver al Inicio
        </Link>
      </div>
    </div>
  );
}

export default Unauthorized;