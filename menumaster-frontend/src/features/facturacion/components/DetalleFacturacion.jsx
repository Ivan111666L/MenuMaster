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
  const totalPorPersona = (totalPedido / Math.max(1, Number(numeroPersonas) || 1)).toFixed(2);

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