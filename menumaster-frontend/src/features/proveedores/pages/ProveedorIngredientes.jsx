import React, { useEffect, useState } from 'react';
import proveedorService from '../services/proveedorService';
import ingredienteService from '@/features/inventario/services/ingredienteService';
import Button from '@/components/Button';

function ProveedorIngredientes({ proveedorId }) {
  const [ingredientes, setIngredientes] = useState([]);
  const [allIngredientes, setAllIngredientes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function fetchData() {
      try {
        const proveedor = await proveedorService.getProveedorById(proveedorId);
        setIngredientes(proveedor.ingredientes || []);
        setAllIngredientes(await ingredienteService.getIngredientes());
      } catch {
        setError('Error al cargar ingredientes');
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [proveedorId]);

  // Aquí iría la lógica para asociar ingredientes al proveedor (requiere endpoint backend extra)

  if (loading) return <div>Cargando ingredientes...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h3>Ingredientes que maneja el proveedor</h3>
      <ul>
        {ingredientes.map(ing => (
          <li key={ing.id}>{ing.nombre}</li>
        ))}
      </ul>
      <h4>Todos los ingredientes disponibles</h4>
      <ul>
        {allIngredientes.map(ing => (
          <li key={ing.id}>{ing.nombre}</li>
        ))}
      </ul>
      {/* Aquí podrías agregar controles para asociar/desasociar ingredientes */}
    </div>
  );
}

export default ProveedorIngredientes;
