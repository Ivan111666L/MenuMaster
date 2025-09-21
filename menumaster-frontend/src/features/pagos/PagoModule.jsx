import React, { useState } from 'react';
import PagosLista from './pages/PagosLista';
import PagoCrear from './pages/PagoCrear';
import MetodoPagoCrear from './pages/MetodoPagoCrear';

function PagoModule() {
  const [refresh, setRefresh] = useState(false);

  const handleSuccess = () => setRefresh(r => !r);

  return (
    <div>
      <h1>Pagos y Métodos de Pago</h1>
      <PagoCrear onSuccess={handleSuccess} />
      <MetodoPagoCrear onSuccess={handleSuccess} />
      <PagosLista key={refresh} />
    </div>
  );
}

export default PagoModule;
