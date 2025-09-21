import React, { useState } from 'react';
import FacturasLista from './pages/FacturasLista';
import FacturaDetalle from './pages/FacturaDetalle';

function FacturaModule() {
  const [selectedId, setSelectedId] = useState(null);

  return (
    <div>
      <h1>Facturación</h1>
      {!selectedId && <FacturasLista onSelect={setSelectedId} />}
      {selectedId && <FacturaDetalle facturaId={selectedId} onClose={() => setSelectedId(null)} />}
    </div>
  );
}

export default FacturaModule;
