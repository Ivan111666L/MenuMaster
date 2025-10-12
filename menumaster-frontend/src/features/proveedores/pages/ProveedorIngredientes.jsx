import React, { useEffect, useState } from 'react';
import proveedorService from '../services/proveedorService';
import ingredienteService from '@/features/inventario/services/ingredienteService';
import Button from '@/components/Button';

function ProveedorIngredientes({ proveedorId, modoPedido = false }) {
  const [proveedor, setProveedor] = useState(null);
  const [ingredientes, setIngredientes] = useState([]);
  const [allIngredientes, setAllIngredientes] = useState([]);
  const [nuevoIngredienteId, setNuevoIngredienteId] = useState('');
  const [busqueda, setBusqueda] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [seleccion, setSeleccion] = useState({}); // { [ingredienteId]: { nombre, unidad, cantidad } }

  useEffect(() => {
    async function fetchData() {
      try {
        const prov = await proveedorService.getProveedorById(proveedorId);
        setProveedor(prov);
        setIngredientes(prov.ingredientes || []);
        setAllIngredientes(await ingredienteService.getIngredientes());
      } catch (e) {
        console.error(e);
        setError('Error al cargar ingredientes');
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [proveedorId]);

  const recargarProveedor = async () => {
    const prov = await proveedorService.getProveedorById(proveedorId);
    setProveedor(prov);
    setIngredientes(prov.ingredientes || []);
  };

  const estaAsociado = (ingredienteId) => {
    return Array.isArray(ingredientes) && ingredientes.some(ing => {
      const id = ing.id || ing.ingrediente_id;
      return id === ingredienteId;
    });
  };

  const agregarIngrediente = async () => {
    if (!nuevoIngredienteId) return;
    try {
      await proveedorService.asociarIngrediente(proveedorId, Number(nuevoIngredienteId));
      setNuevoIngredienteId('');
      await recargarProveedor();
    } catch (e) {
      console.error('Error al asociar ingrediente:', e);
      alert('No se pudo asociar el ingrediente.');
    }
  };

  const quitarIngrediente = async (ing) => {
    const id = ing.id || ing.ingrediente_id;
    if (!id) return;
    try {
      await proveedorService.desasociarIngrediente(Number(id));
      await recargarProveedor();
    } catch (e) {
      console.error('Error al desasociar ingrediente:', e);
      alert('No se pudo quitar el ingrediente.');
    }
  };

  const toggleSeleccion = (ing) => {
    const id = ing.id || ing.ingrediente_id;
    const nombre = ing.nombre || ing.ingrediente_nombre || 'Ingrediente';
    const unidad = ing.unidad_medida || '';
    setSeleccion(prev => {
      const nuevo = { ...prev };
      if (nuevo[id]) {
        delete nuevo[id];
      } else {
        nuevo[id] = { nombre, unidad, cantidad: 1 };
      }
      return nuevo;
    });
  };

  const cambiarCantidad = (id, valor) => {
    const cantidad = Math.max(0, Number(valor) || 0);
    setSeleccion(prev => ({
      ...prev,
      [id]: { ...prev[id], cantidad }
    }));
  };

  const pedirPorWhatsApp = () => {
    try {
      const telefono = (proveedor?.telefono || proveedor?.proveedor_telefono || '').replace(/\D/g, '');
      if (!telefono) {
        alert('Este proveedor no tiene teléfono válido configurado.');
        return;
      }
      const items = Object.entries(seleccion)
        .filter(([, data]) => data.cantidad > 0)
        .map(([, data]) => `- ${data.nombre}: ${data.cantidad}${data.unidad ? ' ' + data.unidad : ''}`)
        .join('\n');
      if (!items) {
        alert('Selecciona al menos un ingrediente y cantidad antes de continuar.');
        return;
      }
      const texto = `Hola ${proveedor?.nombre || ''},\n` +
        `Quisiera realizar un pedido.\n\n` +
        `Ingredientes solicitados:\n${items}\n\n` +
        `Por favor confirmar disponibilidad, precio y tiempo de entrega.\n` +
        `Gracias.`;
      const url = `https://wa.me/${telefono}?text=${encodeURIComponent(texto)}`;
      window.open(url, '_blank');
    } catch (e) {
      console.error('No se pudo preparar el mensaje de WhatsApp:', e);
      alert('Ocurrió un error al preparar el mensaje de WhatsApp.');
    }
  };

  if (loading) return <div>Cargando ingredientes...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h3>Ingredientes del Proveedor</h3>
      <div style={{ marginBottom: '8px', fontSize: '0.9em', color: '#555' }}>
        Selecciona ingredientes y cantidades para preparar el pedido por WhatsApp.
      </div>
      <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
        {Array.isArray(ingredientes) && ingredientes.length > 0 ? (
          ingredientes.map(ing => {
            const id = ing.id || ing.ingrediente_id;
            const nombre = ing.nombre || ing.ingrediente_nombre || 'Ingrediente';
            const unidad = ing.unidad_medida || '';
            const seleccionado = !!seleccion[id];
            const cantidad = seleccion[id]?.cantidad || 1;
            return (
              <div key={id} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '6px 0', borderBottom: '1px solid #eee' }}>
                <input
                  type="checkbox"
                  checked={seleccionado}
                  onChange={() => toggleSeleccion(ing)}
                />
                <div style={{ flex: 1 }}>
                  <div style={{ fontWeight: 500 }}>{nombre}</div>
                  {unidad && (<div style={{ fontSize: '0.85em', color: '#666' }}>Unidad: {unidad}</div>)}
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                  <label style={{ fontSize: '0.85em' }}>Cant:</label>
                  <input
                    type="number"
                    min="0"
                    step="0.1"
                    value={cantidad}
                    onChange={(e) => cambiarCantidad(id, e.target.value)}
                    disabled={!seleccionado}
                    style={{ width: '80px' }}
                  />
                </div>
                <Button variant="danger" onClick={() => quitarIngrediente(ing)}>Quitar</Button>
              </div>
            );
          })
        ) : (
          <div>No hay ingredientes asociados a este proveedor.</div>
        )}
      </div>

      <h4 style={{ marginTop: '16px' }}>Todos los ingredientes disponibles</h4>
      <div style={{ marginBottom: '8px' }}>
        <label htmlFor="proveedor-buscar-ingredientes" style={{ display: 'block', marginBottom: '4px' }}>Buscar ingredientes</label>
        <input
          id="proveedor-buscar-ingredientes"
          name="proveedor-buscar-ingredientes"
          type="text"
          placeholder="Buscar ingredientes..."
          aria-label="Buscar ingredientes"
          value={busqueda}
          onChange={(e) => setBusqueda(e.target.value)}
          style={{ width: '100%', padding: '6px' }}
        />
      </div>
      <div style={{ display: 'flex', gap: '8px', alignItems: 'center', marginBottom: '8px' }}>
        <label htmlFor="proveedor-nuevo-ingrediente" style={{ fontSize: '0.9em' }}>Agregar ingrediente</label>
        <select
          id="proveedor-nuevo-ingrediente"
          name="proveedor-nuevo-ingrediente"
          value={nuevoIngredienteId}
          onChange={(e) => setNuevoIngredienteId(e.target.value)}
          style={{ flex: 1, padding: '6px' }}
          aria-label="Seleccionar ingrediente para agregar"
          title="Seleccionar ingrediente para agregar"
        >
          <option value="">Selecciona un ingrediente para agregar</option>
          {Array.isArray(allIngredientes) && allIngredientes
            .filter(ing => ing.nombre?.toLowerCase().includes(busqueda.toLowerCase()))
            .filter(ing => !estaAsociado(ing.id))
            .map(ing => (
              <option key={ing.id} value={ing.id}>{ing.nombre}</option>
            ))}
        </select>
        <Button variant="primary" onClick={agregarIngrediente} disabled={!nuevoIngredienteId}>Agregar</Button>
      </div>
      <ul style={{ maxHeight: '200px', overflowY: 'auto' }}>
        {Array.isArray(allIngredientes) && allIngredientes
          .filter(ing => ing.nombre?.toLowerCase().includes(busqueda.toLowerCase()))
          .map(ing => (
            <li key={ing.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '6px 0' }}>
              <span>{ing.nombre}</span>
              {estaAsociado(ing.id) ? (
                <span style={{ fontSize: '0.85em', color: '#888' }}>Ya asociado</span>
              ) : (
                <Button size="sm" onClick={async () => {
                  try {
                    await proveedorService.asociarIngrediente(proveedorId, ing.id);
                    await recargarProveedor();
                  } catch (e) {
                    console.error('Error al asociar ingrediente:', e);
                    alert('No se pudo asociar el ingrediente.');
                  }
                }}>Agregar</Button>
              )}
            </li>
          ))}
      </ul>

      {modoPedido && (
        <div style={{ marginTop: '12px', display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
          <Button variant="success" onClick={pedirPorWhatsApp}>Pedir por WhatsApp</Button>
        </div>
      )}
    </div>
  );
}

export default ProveedorIngredientes;
