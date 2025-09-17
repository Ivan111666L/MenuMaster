import React from 'react';
import Input from '@/components/Input';
import Button from '@/components/Button';

function DetalleFacturacion({ pedidoSeleccionado, numeroPersonas, setNumeroPersonas, facturar, generarFacturaParaImprimir, generarPagoQR, qrCodeDataUrl }) {
  if (!pedidoSeleccionado) {
    return (
      <div className="detalle-facturacion-vacio">
        <h3>Selecciona un pedido de la lista para procesar el pago.</h3>
      </div>
    );
  }

  const totalPedido = pedidoSeleccionado.items.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
  const totalPorPersona = (totalPedido / numeroPersonas).toFixed(2);

  return (
    <div className="detalle-facturacion">
      <h2>Detalles del Pedido #{pedidoSeleccionado.id}</h2>
      <p><strong>Mesa:</strong> {pedidoSeleccionado.mesa_numero}</p>
      
      <ul className="detalle-items">
        {pedidoSeleccionado.items.map(item => (
          <li key={item.producto_id}>
            <span>{item.cantidad} x {item.nombre_producto}</span>
            <span>${(item.cantidad * item.precio_unitario).toFixed(2)}</span>
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
          <Button onClick={() => facturar(pedidoSeleccionado.id, 'Efectivo')} variant="primary">Facturar (Efectivo)</Button>
          <Button onClick={() => facturar(pedidoSeleccionado.id, 'Tarjeta')} variant="secondary">Facturar (Tarjeta)</Button>
          <Button onClick={generarFacturaParaImprimir}>Imprimir</Button>
          <Button onClick={generarPagoQR}>Generar QR</Button>
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