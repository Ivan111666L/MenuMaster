// --- Componente para mostrar el resumen del pedido ---
// Muestra los productos seleccionados, el total y las acciones para enviar o limpiar el pedido.
import React from 'react';
import Button from '@/components/Button';

// --- Componente principal ---
// Gestiona la visualización y acciones del resumen del pedido.
function PedidoResumen({ pedidoActual, eliminarItem, limpiarPedido, enviarPedido }) {
  // Verificamos que pedidoActual exista, si no, usamos un objeto con items como array vacío
  const pedido = pedidoActual || { items: [] };
  const total = pedido.items.reduce((sum, item) => sum + (item.cantidad * item.precio), 0);

  // --- Renderizado del resumen ---
  // Muestra la tabla de productos, el total y los botones de acción.
  return (
    <div className="pedido-resumen">
      <h2>2. Resumen del Pedido</h2>
      {pedido.items.length === 0 ? (
        <p className="resumen-vacio">Añade productos desde el panel de la izquierda.</p>
      ) : (
        <>
          <table className="resumen-tabla">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Subtotal</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              {pedido.items.map(item => (
                <tr key={item.producto_id}>
                  <td>{item.nombre}</td>
                  <td>{item.cantidad}</td>
                  <td>${(item.cantidad * item.precio).toFixed(2)}</td>
                  <td>
                    <Button variant="danger" onClick={() => eliminarItem(item.producto_id)}>
                      X
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          <div className="resumen-total">
            <strong>Total: ${total.toFixed(2)}</strong>
          </div>
          <div className="resumen-acciones">
            <Button onClick={enviarPedido} variant="primary" className="btn-completar">
              Enviar Pedido a Cocina
            </Button>
            <Button onClick={limpiarPedido} variant="secondary">
              Limpiar Pedido
            </Button>
          </div>
        </>
      )}
    </div>
  );
}

export default PedidoResumen;