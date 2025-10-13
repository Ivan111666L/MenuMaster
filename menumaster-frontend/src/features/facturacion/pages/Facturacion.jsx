import React from 'react';
import { useFacturacion } from '@/features/facturacion/hooks/useFacturacion';
import ListaPedidos from '@/features/pedidos/components/ListaPedidos';
import DetalleFacturacion from '@/features/facturacion//components/DetalleFacturacion';
import PagoCrear from '@/features/pagos/pages/PagoCrear';
import PagosLista from '@/features/pagos/pages/PagosLista';
import Spinner from '@/components/Spinner';
import '@/styles/facturacion.css';

function Facturacion() {
  const facturacionProps = useFacturacion();

  if (facturacionProps.loading) return <Spinner />;
  if (facturacionProps.error) return <div className="error-message">{facturacionProps.error}</div>;

  // Calcular monto sugerido por persona para prellenar el registro de pago
  const { pedidoSeleccionado, numeroPersonas } = facturacionProps;
  let montoSugerido = '';
  if (pedidoSeleccionado) {
    const rawItems = Array.isArray(pedidoSeleccionado.items)
      ? pedidoSeleccionado.items
      : Array.isArray(pedidoSeleccionado.detalles)
        ? pedidoSeleccionado.detalles
        : [];
    const getCantidad = (item) => Number(item?.cantidad ?? 1);
    const getPrecioUnitario = (item) => Number(item?.precio_unitario ?? item?.precio ?? 0);
    const totalPedido = rawItems.reduce((sum, item) => sum + (getCantidad(item) * getPrecioUnitario(item)), 0);
    const porPersona = totalPedido / Math.max(1, Number(numeroPersonas) || 1);
    montoSugerido = porPersona.toFixed(2);
  }

  return (
    <div className="facturacion-container">
      <h1 className="facturacion-title">Facturación</h1>
      <p className="facturacion-description">Selecciona un pedido para procesar el pago.</p>
      <div className="facturacion-content">
        <ListaPedidos pedidos={facturacionProps.pedidos ?? []}
                      pedidoSeleccionado={facturacionProps.pedidoSeleccionado}
                      seleccionarPedido={facturacionProps.seleccionarPedido} />
        <div>
          <DetalleFacturacion pedidoSeleccionado={facturacionProps.pedidoSeleccionado}
                              numeroPersonas={facturacionProps.numeroPersonas}
                              setNumeroPersonas={facturacionProps.setNumeroPersonas}
                              facturar={facturacionProps.facturar}
                              generarFacturaParaImprimir={facturacionProps.generarFacturaParaImprimir}
                              generarPagoQR={facturacionProps.generarPagoQR}
                              qrCodeDataUrl={facturacionProps.qrCodeDataUrl} />
          {/* Sección de pagos integrada para complementar la facturación */}
          <div style={{ marginTop: '1.5rem' }}>
            <PagoCrear
              pedidoId={pedidoSeleccionado?.id ?? null}
              montoSugerido={montoSugerido}
              onSuccess={() => window.dispatchEvent(new Event('pagos:update'))}
            />
          </div>
          <div style={{ marginTop: '1rem' }}>
            <PagosLista pedidoId={pedidoSeleccionado?.id ?? null} />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Facturacion;