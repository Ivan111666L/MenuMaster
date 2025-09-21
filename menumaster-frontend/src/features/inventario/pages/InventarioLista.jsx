import React, { useEffect, useState } from 'react';
import { getInventario, getAlertasStock } from '../services/inventarioService';

function InventarioLista() {
  const [items, setItems] = useState([]);
  const [alertas, setAlertas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    Promise.all([getInventario(), getAlertasStock()])
      .then(([inv, alerts]) => {
        setItems(inv);
        setAlertas(alerts);
      })
      .catch(() => setError('Error al cargar inventario'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Cargando inventario...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Inventario</h2>
      {alertas.length > 0 && (
        <div style={{color:'red'}}>
          <strong>Alertas de stock bajo:</strong>
          <ul>
            {alertas.map(a => <li key={a.id}>{a.nombre}: {a.stock} unidades</li>)}
          </ul>
        </div>
      )}
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>Stock</th>
            <th>Unidad</th>
          </tr>
        </thead>
        <tbody>
          {items.map(item => (
            <tr key={item.id}>
              <td>{item.nombre}</td>
              <td>{item.stock}</td>
              <td>{item.unidad}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default InventarioLista;
