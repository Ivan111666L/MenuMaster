import React from 'react';
import { Link } from 'react-router-dom';
import Button from '@/components/Button';

function InventarioMenu() {
  return (
    <div className="menu-container">
      <h1 className="menu-title">Gestión de Inventario</h1>
      <p className="menu-subtitle">
        Selecciona una opción para administrar los ingredientes de tu negocio.
      </p>
      <div className="menu-card-container">
        <div className="menu-card">
          <h3 className="menu-card-title">Añadir Ingrediente</h3>
          <p className="menu-card-description">Registra un nuevo ingrediente en el sistema de inventario.</p>
          <Link to="/inventario/nuevo">
            <Button variant="primary" className="w-full">Nuevo Ingrediente</Button>
          </Link>
        </div>
        <div className="menu-card">
          <h3 className="menu-card-title">Ver Inventario</h3>
          <p className="menu-card-description">Consulta, busca y revisa el stock de todos tus ingredientes.</p>
          <Link to="/inventario/ver">
            <Button variant="primary" className="w-full">Ver Ingredientes</Button>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default InventarioMenu;