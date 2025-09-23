import React from 'react';
import { Routes, Route } from 'react-router-dom';
import AnalisisLayout from '@/features/analisis/pages/AnalisisLayout';
import RentabilidadProductos from '@/features/analisis/pages/RentabilidadProductos';
import CuadreDiario from '@/features/analisis/pages/CuadreDiario';
import InventarioProveedores from '@/features/analisis/pages/InventarioProveedores';
import ResumenVentas from '@/features/analisis/pages/ResumenVentas';

const AnalisisModule = () => {
  return (
    <Routes>
      <Route path="/" element={<AnalisisLayout />}>
        <Route index element={<ResumenVentas />} />
        <Route path="rentabilidad" element={<RentabilidadProductos />} />
        <Route path="cuadre-diario" element={<CuadreDiario />} />
        <Route path="inventario-proveedores" element={<InventarioProveedores />} />
      </Route>
    </Routes>
  );
};

export default AnalisisModule;