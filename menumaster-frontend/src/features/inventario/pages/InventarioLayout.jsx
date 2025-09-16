import React from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import Button from '@/components/Button';
import '@/styles/inventario.css';

function InventarioLayout() {
  const location = useLocation();
  // El botón "Volver" solo se muestra si no estamos en la página principal de inventario
  const mostrarBotonVolver = location.pathname !== '/inventario';

  return (
    <div className="inventario-app">
      <div className="inventario-container">
        {mostrarBotonVolver && (
          <Link to="/inventario">
            <Button variant="secondary" className="btn-volver">&larr; Volver al Menú</Button>
          </Link>
        )}
        {/* Aquí se renderizarán las rutas hijas (menu, ver, nuevo) */}
        <Outlet />
      </div>
    </div>
  );
}

export default InventarioLayout;