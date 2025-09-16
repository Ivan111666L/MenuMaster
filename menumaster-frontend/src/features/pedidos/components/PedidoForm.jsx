import React from 'react';
import Button from '@/components/Button';

function PedidoForm({ productos, mesas, seleccionarMesa, agregarItem, pedidoActual }) {
  return (
    <div className="pedido-form">
      <h2>1. Selecciona Mesa y Productos</h2>
      <div className="form-group">
        <label htmlFor="mesa">Mesa</label>
        <select id="mesa" className="form-input" value={pedidoActual.mesa_id} onChange={(e) => seleccionarMesa(e.target.value)}>
          <option value="">-- Selecciona una mesa --</option>
          {mesas.map(mesa => (
            <option key={mesa.id} value={mesa.id}>
              {mesa.numero} ({mesa.ubicacion})
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label>Productos Disponibles</label>
        <div className="productos-disponibles-lista">
          {productos.map(producto => (
            <Button key={producto.id} onClick={() => agregarItem(producto)}>
              {producto.nombre}
            </Button>
          ))}
        </div>
      </div>
    </div>
  );
}

export default PedidoForm;