<<<<<<< HEAD
// --- Componente para seleccionar ingredientes ---
// Permite al usuario elegir ingredientes y cantidades para un producto.
import React, { useState, useEffect } from 'react';
import ingredienteService from '@/features/inventario/services/ingredienteService';
import Button from '@/components/Button';
import '@/styles/productos.css';

// --- Componente principal ---
// Gestiona la selección, búsqueda y cantidad de ingredientes.
=======
import React, { useState, useEffect } from 'react';
import ingredienteService from '@/services/ingredienteService';
import Button from '@/components/Button';
import '@/styles/productos.css';

>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
function SelectorIngredientes({ onIngredientesChange, ingredientesSeleccionados = [] }) {
  const [ingredientes, setIngredientes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [busqueda, setBusqueda] = useState('');
  const [ingredientesSeleccionadosInternos, setIngredientesSeleccionadosInternos] = useState(
<<<<<<< HEAD
    ingredientesSeleccionados || []
  );

  // Cargar ingredientes al montar el componente
  // --- Cargar ingredientes al montar el componente ---
  // Obtiene la lista de ingredientes desde el backend.
=======
    ingredientesSeleccionados
  );

  // Cargar ingredientes al montar el componente
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
  useEffect(() => {
    const cargarIngredientes = async () => {
      try {
        setLoading(true);
        const data = await ingredienteService.getIngredientes();
<<<<<<< HEAD
        setIngredientes(data);
      } catch (err) {
        setError('Error al cargar los ingredientes');
        console.error('Error:', err);
=======
        setIngredientes(Array.isArray(data) ? data : []); // Aseguramos que siempre sea un array
      } catch (err) {
        setError('Error al cargar los ingredientes');
        console.error('Error:', err);
        setIngredientes([]); // En caso de error, inicializamos como array vacío
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
      } finally {
        setLoading(false);
      }
    };

    cargarIngredientes();
  }, []);

<<<<<<< HEAD
  // Filtrar ingredientes por búsqueda
  // --- Filtrar ingredientes por búsqueda ---
  // Permite buscar ingredientes por nombre.
  const ingredientesFiltrados = Array.isArray(ingredientes)
    ? ingredientes.filter(ingrediente =>
        ingrediente.nombre && ingrediente.nombre.toLowerCase().includes(busqueda.toLowerCase())
      )
    : [];

  // Manejar selección/deselección de ingredientes
  // --- Manejar selección/deselección de ingredientes ---
  // Permite agregar o quitar ingredientes seleccionados.
=======
  // Actualizar ingredientes seleccionados cuando cambien las props
  useEffect(() => {
    setIngredientesSeleccionadosInternos(ingredientesSeleccionados);
  }, [ingredientesSeleccionados]);

  // Filtrar ingredientes por búsqueda
  const ingredientesFiltrados = ingredientes.filter(ingrediente =>
    ingrediente.nombre.toLowerCase().includes(busqueda.toLowerCase())
  );

  // Manejar selección/deselección de ingredientes
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
  const toggleIngrediente = (ingrediente) => {
    const yaSeleccionado = ingredientesSeleccionadosInternos.find(
      ing => ing.id === ingrediente.id
    );

    let nuevosSeleccionados;
    if (yaSeleccionado) {
      // Remover ingrediente
      nuevosSeleccionados = ingredientesSeleccionadosInternos.filter(
        ing => ing.id !== ingrediente.id
      );
    } else {
      // Agregar ingrediente con cantidad por defecto
      nuevosSeleccionados = [
        ...ingredientesSeleccionadosInternos,
        { ...ingrediente, cantidad: 1 }
      ];
    }

    setIngredientesSeleccionadosInternos(nuevosSeleccionados);
    onIngredientesChange(nuevosSeleccionados);
  };

  // Actualizar cantidad de un ingrediente seleccionado
<<<<<<< HEAD
  // --- Actualizar cantidad de un ingrediente seleccionado ---
  // Permite modificar la cantidad de cada ingrediente elegido.
=======
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
  const actualizarCantidad = (ingredienteId, nuevaCantidad) => {
    const nuevosSeleccionados = ingredientesSeleccionadosInternos.map(ing =>
      ing.id === ingredienteId
        ? { ...ing, cantidad: Math.max(0.1, Number(nuevaCantidad)) }
        : ing
    );

    setIngredientesSeleccionadosInternos(nuevosSeleccionados);
    onIngredientesChange(nuevosSeleccionados);
  };

<<<<<<< HEAD
  // --- Renderizado condicional ---
  // Muestra mensajes de carga o error si corresponde.
=======
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
  if (loading) {
    return <div className="selector-ingredientes-loading">Cargando ingredientes...</div>;
  }

  if (error) {
    return <div className="selector-ingredientes-error">{error}</div>;
  }

<<<<<<< HEAD
  // --- Renderizado del selector ---
  // Muestra la interfaz para buscar, seleccionar y ajustar ingredientes.
=======
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
  return (
    <div className="selector-ingredientes">
      <h3>Seleccionar Ingredientes</h3>
      
      {/* Buscador */}
      <div className="ingredientes-busqueda">
        <input
          type="text"
          placeholder="Buscar ingredientes..."
          value={busqueda}
          onChange={(e) => setBusqueda(e.target.value)}
          className="form-input"
        />
      </div>

      {/* Lista de ingredientes disponibles */}
      <div className="ingredientes-disponibles">
        <h4>Ingredientes Disponibles</h4>
        <div className="ingredientes-grid">
          {ingredientesFiltrados.map(ingrediente => {
            const estaSeleccionado = ingredientesSeleccionadosInternos.find(
              ing => ing.id === ingrediente.id
            );

            return (
              <div
                key={ingrediente.id}
                className={`ingrediente-card ${estaSeleccionado ? 'seleccionado' : ''}`}
                onClick={() => toggleIngrediente(ingrediente)}
              >
                <div className="ingrediente-info">
                  <span className="ingrediente-nombre">{ingrediente.nombre}</span>
                  <span className="ingrediente-stock">
                    Stock: {ingrediente.stock_actual} {ingrediente.unidad_medida}
                  </span>
                </div>
                {estaSeleccionado && (
                  <div className="ingrediente-seleccionado-badge">✓</div>
                )}
              </div>
            );
          })}
        </div>
      </div>

      {/* Lista de ingredientes seleccionados */}
      {ingredientesSeleccionadosInternos.length > 0 && (
        <div className="ingredientes-seleccionados">
          <h4>Ingredientes Seleccionados ({ingredientesSeleccionadosInternos.length})</h4>
          <div className="ingredientes-seleccionados-lista">
            {ingredientesSeleccionadosInternos.map(ingrediente => (
              <div key={ingrediente.id} className="ingrediente-seleccionado">
                <span className="ingrediente-nombre">{ingrediente.nombre}</span>
                <div className="ingrediente-cantidad-control">
                  <label>Cantidad:</label>
                  <input
                    type="number"
                    min="0.1"
                    step="0.1"
                    value={ingrediente.cantidad}
                    onChange={(e) => actualizarCantidad(ingrediente.id, e.target.value)}
                    className="form-input cantidad-input"
                  />
                  <span className="unidad">{ingrediente.unidad_medida}</span>
                </div>
                <Button
                  variant="danger"
                  size="small"
                  onClick={(e) => {
                    e.stopPropagation();
                    toggleIngrediente(ingrediente);
                  }}
                >
                  Quitar
                </Button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

export default SelectorIngredientes;