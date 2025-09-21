import React, { useState } from 'react';
import Button from '@/components/Button';

<<<<<<< HEAD
function PedidoForm({ productos, mesas, seleccionarMesa, agregarItem, pedidoActual }) {
=======
function PedidoForm({ productos, mesas, seleccionarMesa, agregarItem, pedidoActual, enviarPedidoCocina, generarRecibo }) {
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
  // Verificamos que pedidoActual exista, si no, usamos un objeto vacío con mesa_id como string vacío
  const pedido = pedidoActual || { mesa_id: '' };
  const [categoriaSeleccionada, setCategoriaSeleccionada] = useState('');
  
  // Agrupar productos por categoría
  const agruparProductosPorCategoria = () => {
    if (!productos) return {};
    
    return productos.reduce((grupos, producto) => {
      const categoria = producto.categoria_nombre || 'Sin Categoría';
      if (!grupos[categoria]) {
        grupos[categoria] = [];
      }
      grupos[categoria].push(producto);
      return grupos;
    }, {});
  };

  // Ordenar categorías en el orden específico
  const ordenCategorias = ['Entradas', 'Platos Fuertes', 'Bebidas', 'Postres'];
  
  const productosAgrupados = agruparProductosPorCategoria();
  const categoriasOrdenadas = ordenCategorias.filter(cat => productosAgrupados[cat]);
  const otrasCategoriasOrdenadas = Object.keys(productosAgrupados)
    .filter(cat => !ordenCategorias.includes(cat))
    .sort();
  
  const todasCategorias = [...categoriasOrdenadas, ...otrasCategoriasOrdenadas];
  
  // Función para filtrar productos según categoría seleccionada
  const getProductosFiltrados = () => {
    if (!categoriaSeleccionada || categoriaSeleccionada === 'todas') {
      return productos || [];
    }
    return productosAgrupados[categoriaSeleccionada] || [];
  };
  
  return (
    <div className="pedido-form">
      <h2>1. Selecciona Mesa y Productos</h2>
      
      {/* Selección de Mesa */}
      <div className="form-group">
        <label htmlFor="mesa">Mesa</label>
        <select 
          id="mesa" 
          className="form-input" 
          value={pedido.mesa_id} 
          onChange={(e) => seleccionarMesa(e.target.value)}
        >
          <option value="">-- Selecciona una mesa --</option>
          {mesas && mesas.map(mesa => (
            <option key={mesa.id} value={mesa.id}>
              Mesa {mesa.numero} ({mesa.ubicacion})
            </option>
          ))}
        </select>
      </div>

      {/* Filtro por Categorías */}
      <div className="form-group">
        <label htmlFor="categoria">Filtrar por Categoría</label>
        <div className="categoria-filtros">
          <button 
            className={`categoria-btn ${categoriaSeleccionada === 'todas' || categoriaSeleccionada === '' ? 'active' : ''}`}
            onClick={() => setCategoriaSeleccionada('todas')}
          >
            Todas
          </button>
          {todasCategorias.map(categoria => (
            <button 
              key={categoria}
              className={`categoria-btn ${categoriaSeleccionada === categoria ? 'active' : ''}`}
              onClick={() => setCategoriaSeleccionada(categoria)}
            >
              {categoria}
            </button>
          ))}
        </div>
      </div>

      {/* Lista de Productos por Categoría */}
      <div className="form-group">
        <label>
          {categoriaSeleccionada && categoriaSeleccionada !== 'todas' 
            ? `Productos - ${categoriaSeleccionada}` 
            : 'Productos Disponibles'
          }
        </label>
        
        {categoriaSeleccionada === '' || categoriaSeleccionada === 'todas' ? (
          // Mostrar productos agrupados por categoría
          <div className="productos-por-categoria">
            {todasCategorias.map(categoria => (
              <div key={categoria} className="categoria-seccion">
                <h3 className="categoria-titulo">{categoria}</h3>
                <div className="productos-categoria-lista">
                  {productosAgrupados[categoria].map(producto => (
                    <div key={producto.id} className="producto-card">
                      <button 
                        className="producto-btn"
                        onClick={() => agregarItem(producto)}
                      >
                        <div className="producto-info">
                          <span className="producto-nombre">{producto.nombre}</span>
                          <span className="producto-precio">${producto.precio}</span>
                        </div>
                        {producto.descripcion && (
                          <span className="producto-descripcion">{producto.descripcion}</span>
                        )}
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        ) : (
          // Mostrar productos de una categoría específica
          <div className="productos-categoria-lista">
            {getProductosFiltrados().map(producto => (
              <div key={producto.id} className="producto-card">
                <button 
                  className="producto-btn"
                  onClick={() => agregarItem(producto)}
                >
                  <div className="producto-info">
                    <span className="producto-nombre">{producto.nombre}</span>
                    <span className="producto-precio">${producto.precio}</span>
                  </div>
                  {producto.descripcion && (
                    <span className="producto-descripcion">{producto.descripcion}</span>
                  )}
                </button>
              </div>
            ))}
          </div>
        )}
<<<<<<< HEAD
=======
        {/* Botones para acciones de mesero */}
        <div className="form-group" style={{ marginTop: '2rem' }}>
          <Button onClick={enviarPedidoCocina} color="primary">Enviar a Cocina</Button>
          <Button onClick={generarRecibo} color="secondary" style={{ marginLeft: '1rem' }}>Generar Recibo de Facturación</Button>
        </div>
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
      </div>
    </div>
  );
}

export default PedidoForm;