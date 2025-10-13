import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Input from '@/components/Input';
import Button from '@/components/Button';
import feService from '@/features/facturacion/services/facturacionElectronicaService';

function DetalleFacturacion({ pedidoSeleccionado, numeroPersonas, setNumeroPersonas, facturar, generarFacturaParaImprimir, generarPagoQR, qrCodeDataUrl }) {
  if (!pedidoSeleccionado) {
    return (
      <div className="detalle-facturacion-vacio">
        <h3>Selecciona un pedido de la lista para procesar el pago.</h3>
      </div>
    );
  }

  // Normalizamos la lista de items/detalles para evitar errores cuando 'items' sea undefined
  const rawItems = Array.isArray(pedidoSeleccionado.items)
    ? pedidoSeleccionado.items
    : Array.isArray(pedidoSeleccionado.detalles)
      ? pedidoSeleccionado.detalles
      : [];

  const getNombre = (item) => item?.nombre_producto ?? item?.producto_nombre ?? item?.nombre ?? 'Producto';
  const getCantidad = (item) => Number(item?.cantidad ?? 1);
  const getPrecioUnitario = (item) => Number(item?.precio_unitario ?? item?.precio ?? 0);

  const totalPedido = rawItems.reduce((sum, item) => sum + (getCantidad(item) * getPrecioUnitario(item)), 0);
  const personasSanitizadas = Math.max(1, Number(numeroPersonas) || 1);
  if (!Number.isFinite(Number(numeroPersonas))) {
    console.warn('DetalleFacturacion: numeroPersonas inválido, valor recibido:', numeroPersonas);
  }
  const totalPorPersona = (totalPedido / personasSanitizadas).toFixed(2);

  const [emailCliente, setEmailCliente] = useState(pedidoSeleccionado?.cliente_email || '');
  const [enviando, setEnviando] = useState(false);
  const [estadoFE, setEstadoFE] = useState(null);

  const emitirFE = async () => {
    if (!pedidoSeleccionado?.id) return;
    setEnviando(true);
    try {
      const res = await feService.emitirFacturaElectronica(pedidoSeleccionado.id, emailCliente || undefined);
      setEstadoFE(res?.data ?? res);
      alert('Factura electrónica emitida. CUFE: ' + (res?.data?.cufe || res?.cufe || 'N/A'));
    } catch (e) {
      alert('Error al emitir factura electrónica');
    } finally {
      setEnviando(false);
    }
  };

  const enviarCorreoFE = async () => {
    if (!pedidoSeleccionado?.id || !emailCliente) {
      alert('Ingrese un correo válido');
      return;
    }
    setEnviando(true);
    try {
      const res = await feService.enviarFacturaPorCorreo(pedidoSeleccionado.id, emailCliente);
      alert('Comprobante enviado a ' + emailCliente);
    } catch (e) {
      alert('Error al enviar correo');
    } finally {
      setEnviando(false);
    }
  };

  return (
    <div className="detalle-facturacion">
      <h2>Detalles del Pedido #{pedidoSeleccionado.id}</h2>
      <p><strong>Mesa:</strong> {pedidoSeleccionado.mesa_numero}</p>
      
      <ul className="detalle-items">
        {rawItems.map((item, idx) => (
          <li key={item?.producto_id ?? item?.id ?? idx}>
            <span>{getCantidad(item)} x {getNombre(item)}</span>
            <span>${(getCantidad(item) * getPrecioUnitario(item)).toFixed(2)}</span>
          </li>
        ))}
      </ul>

      <div className="detalle-total">
        <strong>Total General: ${totalPedido.toFixed(2)}</strong>
      </div>

      <div className="acciones-facturacion">
        <h3>Opciones de Pago</h3>
        <Input id="dividir" label="Dividir cuenta entre:" type="number" value={numeroPersonas}
          onChange={(e) => setNumeroPersonas(Math.max(1, parseInt(e.target.value) || 1))}
          min="1" />
        {numeroPersonas > 1 && (<h4 className="total-dividido">Total por persona: ${totalPorPersona}</h4>)}
        
        <div className="botones-accion">
          {/* Enviamos metodo_id explícito para evitar errores en backend */}
          <Button onClick={() => facturar(pedidoSeleccionado.id, 'Efectivo', 1)} variant="primary">Facturar (Efectivo)</Button>
          <Button onClick={() => facturar(pedidoSeleccionado.id, 'Tarjeta de Crédito', 2)} variant="secondary">Facturar (Tarjeta)</Button>
          <Button onClick={generarFacturaParaImprimir}>Imprimir</Button>
          <Button onClick={generarPagoQR}>Generar QR</Button>
          <Button onClick={emitirFE} disabled={enviando} variant="success">Emitir Factura Electrónica</Button>
        </div>

        <div className="fe-section" style={{ marginTop: '1rem' }}>
          <h3>Factura Electrónica DIAN</h3>
          <Input id="correo-fe" label="Correo del cliente" type="email" value={emailCliente}
                 onChange={(e) => setEmailCliente(e.target.value)} placeholder="cliente@correo.com" />
          <div className="botones-accion" style={{ marginTop: '0.5rem' }}>
            <Button onClick={enviarCorreoFE} disabled={enviando}>Enviar comprobante al correo</Button>
          </div>
          {estadoFE && (
            <div className="fe-estado" style={{ marginTop: '0.5rem' }}>
              <small>Estado: emitida | Número: {estadoFE?.numero} | CUFE: {estadoFE?.cufe}</small>
            </div>
          )}
        </div>

        {qrCodeDataUrl && (
          <div className="qr-container">
            <h4>Escanea para pagar</h4>
            <img src={qrCodeDataUrl} alt="Código QR de pago" />
            <p>Monto: ${totalPorPersona}</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default DetalleFacturacion;

DetalleFacturacion.propTypes = {
  pedidoSeleccionado: PropTypes.shape({
    id: PropTypes.oneOfType([PropTypes.number, PropTypes.string]).isRequired,
    mesa_numero: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    items: PropTypes.array,
    detalles: PropTypes.array,
    cliente_email: PropTypes.string,
  }),
  numeroPersonas: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
  setNumeroPersonas: PropTypes.func,
  facturar: PropTypes.func,
  generarFacturaParaImprimir: PropTypes.func,
  generarPagoQR: PropTypes.func,
  qrCodeDataUrl: PropTypes.string,
};

DetalleFacturacion.defaultProps = {
  pedidoSeleccionado: null,
  numeroPersonas: 1,
  setNumeroPersonas: () => {},
  facturar: () => {},
  generarFacturaParaImprimir: () => {},
  generarPagoQR: () => {},
  qrCodeDataUrl: '',
};