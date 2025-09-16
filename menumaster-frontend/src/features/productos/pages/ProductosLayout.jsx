import React from 'react';
import { Outlet } from 'react-router-dom';

function ProductosLayout() {
  // Este componente actúa como un 'cascarón' para las vistas de productos.
  // El <Outlet /> renderizará el menú, el formulario o la lista, según la URL.
  return (
    <div>
      <Outlet />
    </div>
  );
}

export default ProductosLayout;