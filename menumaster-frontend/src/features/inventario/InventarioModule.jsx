import React, { useState } from 'react';
import InventarioLista from './pages/InventarioLista';
import InventarioMovimientos from './pages/InventarioMovimientos';

function InventarioModule() {
  const [view, setView] = useState('lista');

  return (
    <div>
      <h1>Inventario</h1>
      <div style={{marginBottom:16}}>
        <button onClick={() => setView('lista')}>Ver Inventario</button>
        <button onClick={() => setView('movimientos')} style={{marginLeft:8}}>Ver Movimientos</button>
      </div>
      {view === 'lista' && <InventarioLista />}
      {view === 'movimientos' && <InventarioMovimientos />}
    </div>
  );
}

export default InventarioModule;
