import React from 'react';
import { Link } from 'react-router-dom';
import Button from '@/components/Button'; // Importamos el botón reutilizable
import '@/styles/productos.css'; // Un CSS dedicado para los formularios de productos

function ProductosMenu() {
  return (
    <div className="productos-container">
      <h1 className="productos-title">Gestión de Productos</h1>

      <div className="card-container">
        <div className="card">
          <h3 className="card-title">Productos Nuevos</h3>
          <p>Crea nuevos platos, bebidas o postres para tu menú.</p>
          <Link to="/productos/nuevos" className="card-link">
            <Button variant="primary">Crear Producto</Button>
          </Link>
        </div>

        <div className="card">
          <h3 className="card-title">Productos Creados</h3>
          <p>Administra las entradas, platos fuertes, bebidas y postres existentes.</p>
          <Link to="/productos/creados" className="card-link">
            <Button variant="primary">Ver Productos</Button>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default ProductosMenu;