import React, { useState, useEffect } from 'react';
import { Card, Table, Alert, Spinner, Form, InputGroup, Badge } from 'react-bootstrap';
import { getInventarioConProveedores } from '@/features/analisis/services/analisisService';

const InventarioProveedores = () => {
  const [inventario, setInventario] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filtro, setFiltro] = useState('');
  const [inventarioFiltrado, setInventarioFiltrado] = useState([]);

  useEffect(() => {
    cargarInventario();
  }, []);

  useEffect(() => {
    if (inventario.length > 0) {
      filtrarInventario();
    }
  }, [filtro, inventario]);

  const cargarInventario = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await getInventarioConProveedores();
      if (response.status === 'success') {
        setInventario(response.data);
        setInventarioFiltrado(response.data);
      } else {
        setError(response.message || 'Error al cargar los datos');
      }
    } catch (err) {
      setError('Error de conexión al servidor');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const filtrarInventario = () => {
    if (!filtro) {
      setInventarioFiltrado(inventario);
      return;
    }

    const terminoBusqueda = filtro.toLowerCase();
    const resultados = inventario.filter(item => 
      item.nombre.toLowerCase().includes(terminoBusqueda) ||
      (item.proveedor_nombre && item.proveedor_nombre.toLowerCase().includes(terminoBusqueda))
    );
    
    setInventarioFiltrado(resultados);
  };

  const formatearMoneda = (valor) => {
    return parseFloat(valor).toLocaleString('es-CO', {
      style: 'currency',
      currency: 'COP',
      minimumFractionDigits: 0
    });
  };

  const getNivelStockClass = (stockActual) => {
    const stock = parseFloat(stockActual);
    if (stock <= 10) return 'danger';
    if (stock <= 30) return 'warning';
    return 'success';
  };

  return (
    <div>
      <h3 className="mb-4">Inventario y Proveedores</h3>
      
      <Card className="mb-4 shadow-sm">
        <Card.Body>
          <Form.Group className="mb-3">
            <InputGroup>
              <InputGroup.Text>
                <i className="bi bi-search"></i>
              </InputGroup.Text>
              <Form.Control
                type="text"
                placeholder="Buscar por nombre de ingrediente o proveedor..."
                value={filtro}
                onChange={(e) => setFiltro(e.target.value)}
              />
            </InputGroup>
          </Form.Group>

          {loading && (
            <div className="text-center my-5">
              <Spinner animation="border" variant="primary" />
              <p className="mt-2">Cargando datos de inventario...</p>
            </div>
          )}

          {error && (
            <Alert variant="danger">{error}</Alert>
          )}

          {!loading && !error && inventarioFiltrado.length === 0 && (
            <Alert variant="info">
              No se encontraron ingredientes que coincidan con su búsqueda.
            </Alert>
          )}

          {!loading && !error && inventarioFiltrado.length > 0 && (
            <div className="table-responsive">
              <Table striped hover>
                <thead>
                  <tr>
                    <th>Ingrediente</th>
                    <th>Unidad</th>
                    <th>Stock Actual</th>
                    <th>Precio Unitario</th>
                    <th>Proveedor</th>
                    <th>Contacto</th>
                    <th>Productos</th>
                  </tr>
                </thead>
                <tbody>
                  {inventarioFiltrado.map((item, index) => (
                    <tr key={index}>
                      <td>{item.nombre}</td>
                      <td>{item.unidad_medida}</td>
                      <td>
                        <Badge bg={getNivelStockClass(item.stock_actual)}>
                          {item.stock_actual} {item.unidad_medida}
                        </Badge>
                      </td>
                      <td>{formatearMoneda(item.precio_unitario)}</td>
                      <td>{item.proveedor_nombre || 'No asignado'}</td>
                      <td>
                        {item.proveedor_telefono && (
                          <div><i className="bi bi-telephone me-1"></i>{item.proveedor_telefono}</div>
                        )}
                        {item.proveedor_email && (
                          <div><i className="bi bi-envelope me-1"></i>{item.proveedor_email}</div>
                        )}
                      </td>
                      <td>
                        {item.productos && item.productos.length > 0 ? (
                          <div style={{ maxHeight: '100px', overflowY: 'auto' }}>
                            {item.productos.map((producto, idx) => (
                              <div key={idx} className="mb-1">
                                <small>
                                  {producto.nombre} ({producto.cantidad_requerida} {item.unidad_medida})
                                </small>
                              </div>
                            ))}
                          </div>
                        ) : (
                          <small className="text-muted">No utilizado en productos</small>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </Table>
            </div>
          )}
        </Card.Body>
      </Card>

      <Card className="shadow-sm">
        <Card.Body>
          <h5 className="mb-3">Resumen de Inventario</h5>
          <div className="row g-3">
            <div className="col-md-4">
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Total Ingredientes</h6>
                  <h3>{inventario.length}</h3>
                </Card.Body>
              </Card>
            </div>
            <div className="col-md-4">
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Ingredientes Críticos</h6>
                  <h3>{inventario.filter(item => parseFloat(item.stock_actual) <= 10).length}</h3>
                  <small className="text-danger">Stock bajo (≤ 10 unidades)</small>
                </Card.Body>
              </Card>
            </div>
            <div className="col-md-4">
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Valor Total Inventario</h6>
                  <h3>
                    {formatearMoneda(
                      inventario.reduce((total, item) => 
                        total + (parseFloat(item.stock_actual) * parseFloat(item.precio_unitario)), 0)
                    )}
                  </h3>
                </Card.Body>
              </Card>
            </div>
          </div>
        </Card.Body>
      </Card>
    </div>
  );
};

export default InventarioProveedores;