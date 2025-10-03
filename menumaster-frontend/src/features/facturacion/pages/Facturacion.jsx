import React from 'react';
import { Link } from 'react-router-dom';
import { useFacturacion } from '@/features/facturacion/hooks/useFacturacion';
import ListaPedidos from '@/features/pedidos/components/ListaPedidos';
import DetalleFacturacion from '@/features/facturacion//components/DetalleFacturacion';
import Spinner from '@/components/Spinner';
import '@/styles/facturacion.css';

function Facturacion() {
  const facturacionProps = useFacturacion();

  if (facturacionProps.loading) return <Spinner />;
  if (facturacionProps.error) return <div className="error-message">{facturacionProps.error}</div>;

  return (
    <div className="facturacion-container">
      <h1 className="facturacion-title">Facturación</h1>
      <div className="facturacion-actions">
        <Link to="/facturacion/pagos">Ir a Pagos</Link>
      </div>
      <p className="facturacion-description">Selecciona un pedido para procesar el pago.</p>
      <div className="facturacion-content">
        <ListaPedidos {...facturacionProps} />
        <DetalleFacturacion {...facturacionProps} />
      </div>
    </div>
  );
};

export default Facturacion;