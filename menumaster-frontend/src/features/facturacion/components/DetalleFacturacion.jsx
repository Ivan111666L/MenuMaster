import React from 'react';
import Input from '@/components/Input';
import Button from '@/components/Button';

const DetalleFacturacion = ({
    pedidoSeleccionado,
    numeroPersonas,
    setNumeroPersonas,
    facturar, // Función unificada para facturar
    generarPagoQR,
    qrCodeDataUrl
}) => {

    // Si no hay ningún pedido seleccionado, muestra un mensaje de ayuda.
    if (!pedidoSeleccionado) {
        return (
            <div className="detalle-facturacion-vacio">
                <h3>Selecciona un pedido de la lista para ver los detalles y facturar.</h3>
            </div>
        );
    }

    // --- CORRECCIÓN: Cálculo de total robusto ---
    // Calculamos el total sumando los ítems. Esto es más seguro que depender
    // de una propiedad 'total' que podría no venir de la API.
    const totalPedido = pedidoSeleccionado.items.reduce(
        (sum, item) => sum + (item.cantidad * item.precio_unitario),
        0
    );
    const totalPorPersona = (totalPedido / numeroPersonas);

    return (
        <div className="detalle-facturacion">
            <h2>Detalles del Pedido #{pedidoSeleccionado.id}</h2>
            <p><strong>Mesa:</strong> {pedidoSeleccionado.mesa_numero}</p>
            
            <ul className="detalle-items">
                {pedidoSeleccionado.items.map(item => (
                    // Usamos producto_id si el ID del item no es único
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
                
                {/* CORRECCIÓN: Se usa el componente <Input> reutilizable */}
                <Input
                    id="dividir"
                    label="Dividir la cuenta entre (personas):"
                    type="number"
                    value={numeroPersonas}
                    onChange={(e) => setNumeroPersonas(Math.max(1, parseInt(e.target.value) || 1))}
                    min="1"
                />
                
                {numeroPersonas > 1 && (
                    <h4 className="total-dividido">Total por persona: ${totalPorPersona.toFixed(2)}</h4>
                )}
                
                {/* CORRECCIÓN: Se usan los componentes <Button> reutilizables */}
                <div className="botones-accion">
                    <Button onClick={() => facturar('Efectivo')} variant="primary">Facturar (Efectivo)</Button>
                    <Button onClick={() => facturar('Tarjeta')} variant="secondary">Facturar (Tarjeta)</Button>
                    <Button onClick={generarPagoQR} variant="secondary">Generar QR</Button>
                </div>

                {qrCodeDataUrl && (
                    <div className="qr-container">
                        <h4>Escanea para pagar</h4>
                        <img src={qrCodeDataUrl} alt="Código QR de pago" />
                        <p>Monto: ${totalPorPersona.toFixed(2)}</p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default DetalleFacturacion;