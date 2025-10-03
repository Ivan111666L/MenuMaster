import React, { useState, useEffect } from 'react';
import { Row, Col, Form, Button, Table, Alert, Spinner } from 'react-bootstrap';
import { getRentabilidadProductos } from '@/features/analisis/services/analisisService';
import { Bar } from 'react-chartjs-2';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const RentabilidadProductos = () => {
  const [fechaInicio, setFechaInicio] = useState('');
  const [fechaFin, setFechaFin] = useState('');
  const [productos, setProductos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Establecer fechas predeterminadas (último mes)
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth() - 1, hoy.getDate());
    
    setFechaInicio(inicioMes.toISOString().split('T')[0]);
    setFechaFin(hoy.toISOString().split('T')[0]);
    
    cargarRentabilidad(inicioMes.toISOString().split('T')[0], hoy.toISOString().split('T')[0]);
  }, []);

  const cargarRentabilidad = async (inicio, fin) => {
    try {
      setLoading(true);
      setError(null);
      const response = await getRentabilidadProductos(inicio, fin);
      if (response.status === 'success') {
        setProductos(response.data);
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

  const handleSubmit = (e) => {
    e.preventDefault();
    cargarRentabilidad(fechaInicio, fechaFin);
  };

  // Preparar datos para el gráfico
  const prepararDatosGrafico = () => {
    if (!productos || productos.length === 0) return null;

    // Tomar los 10 productos más rentables
    const topProductos = [...productos].sort((a, b) => 
      parseFloat(b.rentabilidad_total) - parseFloat(a.rentabilidad_total)
    ).slice(0, 10);

    const labels = topProductos.map(item => item.producto_nombre);
    const rentabilidadData = topProductos.map(item => parseFloat(item.rentabilidad_total));
    const ventasData = topProductos.map(item => parseFloat(item.ventas_totales));
    const costosData = topProductos.map(item => parseFloat(item.costos_totales));
    const gananciaUnitData = topProductos.map(item => {
      const precio = parseFloat(item.precio_promedio || 0);
      const costoUnit = parseFloat(item.costo_fabricacion || 0);
      return precio - costoUnit;
    });

    return {
      labels,
      datasets: [
        {
          label: 'Ventas',
          data: ventasData,
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        },
        {
          label: 'Costos',
          data: costosData,
          backgroundColor: 'rgba(255, 99, 132, 0.5)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1
        },
        {
          label: 'Rentabilidad',
          data: rentabilidadData,
          backgroundColor: 'rgba(75, 192, 192, 0.5)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        },
        {
          label: 'Ganancia por Plato',
          data: gananciaUnitData,
          backgroundColor: 'rgba(255, 206, 86, 0.5)',
          borderColor: 'rgba(255, 206, 86, 1)',
          borderWidth: 1
        }
      ]
    };
  };

  const formatearMoneda = (valor) => {
    return parseFloat(valor).toLocaleString('es-CO', {
      style: 'currency',
      currency: 'COP',
      minimumFractionDigits: 0
    });
  };

  const calcularGananciaPorPlato = (producto) => {
    const precio = parseFloat(producto?.precio_promedio || 0);
    const costoUnit = parseFloat(producto?.costo_fabricacion || 0);
    return precio - costoUnit;
  };

  const calcularMargenUnitario = (producto) => {
    const precio = parseFloat(producto?.precio_promedio || 0);
    const gananciaUnit = calcularGananciaPorPlato(producto);
    if (precio <= 0) return 0;
    return (gananciaUnit / precio) * 100;
  };

  return (
    <div className="analisis-section">
      <h3 className="mb-4">Rentabilidad de Productos</h3>
      
      <Form onSubmit={handleSubmit} className="mb-4 analisis-filters">
        <Row>
          <Col md={4}>
            <Form.Group className="mb-3">
              <Form.Label>Fecha Inicio</Form.Label>
              <Form.Control
                type="date"
                value={fechaInicio}
                onChange={(e) => setFechaInicio(e.target.value)}
              />
            </Form.Group>
          </Col>
          <Col md={4}>
            <Form.Group className="mb-3">
              <Form.Label>Fecha Fin</Form.Label>
              <Form.Control
                type="date"
                value={fechaFin}
                onChange={(e) => setFechaFin(e.target.value)}
              />
            </Form.Group>
          </Col>
          <Col md={4} className="d-flex align-items-end">
            <Button type="submit" variant="primary" className="mb-3">
              Consultar
            </Button>
          </Col>
        </Row>
      </Form>

      {loading && (
        <div className="text-center my-5">
          <Spinner animation="border" variant="primary" />
          <p className="mt-2">Cargando datos...</p>
        </div>
      )}

      {error && (
        <Alert variant="danger">{error}</Alert>
      )}

      {!loading && !error && productos.length > 0 && (
        <>
          <div className="section-block">
            <h5 className="mb-3">Top 10 Productos por Rentabilidad</h5>
            {prepararDatosGrafico() && (
              <Bar 
                data={prepararDatosGrafico()} 
                options={{
                  responsive: true,
                  plugins: {
                    legend: {
                      position: 'top',
                    },
                    title: {
                      display: false
                    }
                  },
                  scales: {
                    x: {
                      ticks: {
                        maxRotation: 45,
                        minRotation: 45
                      }
                    }
                  }
                }}
              />
            )}
          </div>

          <div className="section-block">
            <h5 className="mb-3">Detalle de Rentabilidad por Producto</h5>
            <div className="table-responsive analisis-table">
              <Table striped hover className="mb-0">
                  <thead>
                    <tr>
                      <th>Producto</th>
                      <th>Concurrencia</th>
                      <th>Precio Venta Promedio</th>
                      <th>Costo Unitario</th>
                      <th>Ganancia por Plato</th>
                      <th>Margen Unitario (%)</th>
                      <th>Ventas Totales</th>
                      <th>Costos Totales</th>
                      <th>Rentabilidad Total</th>
                      <th>% Rentabilidad</th>
                    </tr>
                  </thead>
                  <tbody>
                    {productos.map((producto, index) => (
                      <>
                        <tr key={`prod-${index}`}>
                          <td>{producto.producto_nombre}</td>
                          <td>{producto.cantidad_total}</td>
                          <td>{formatearMoneda(producto.precio_promedio)}</td>
                          <td>{formatearMoneda(producto.costo_fabricacion || 0)}</td>
                          <td>{formatearMoneda(calcularGananciaPorPlato(producto))}</td>
                          <td>{calcularMargenUnitario(producto).toFixed(2)}%</td>
                          <td>{formatearMoneda(producto.ventas_totales)}</td>
                          <td>{formatearMoneda(producto.costos_totales)}</td>
                          <td>{formatearMoneda(producto.rentabilidad_total)}</td>
                          <td>{parseFloat(producto.porcentaje_rentabilidad).toFixed(2)}%</td>
                        </tr>
                        <tr key={`ing-${index}`}>
                          <td colSpan="10">
                            {producto.ingredientes && producto.ingredientes.length > 0 ? (
                              <div className="mt-2">
                                <strong>Ingredientes:</strong>
                                <Table size="sm" className="mt-2">
                                  <thead>
                                    <tr>
                                      <th>Ingrediente</th>
                                      <th>Cantidad</th>
                                      <th>Unidad</th>
                                      <th>Costo Unitario</th>
                                      <th>Costo Subtotal</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    {producto.ingredientes.map((ing, idx) => (
                                      <tr key={idx}>
                                        <td>{ing.ingrediente_nombre}</td>
                                        <td>{ing.cantidad}</td>
                                        <td>{ing.unidad_medida}</td>
                                        <td>{formatearMoneda(ing.costo_unitario || 0)}</td>
                                        <td>{formatearMoneda(ing.costo_subtotal || 0)}</td>
                                      </tr>
                                    ))}
                                  </tbody>
                                </Table>
                              </div>
                            ) : (
                              <div className="text-muted">Sin ingredientes definidos para este producto.</div>
                            )}
                          </td>
                        </tr>
                      </>
                    ))}
                  </tbody>
                </Table>
              </div>
          </div>
        </>
      )}

      {!loading && !error && productos.length === 0 && (
        <Alert variant="info">
          No hay datos de rentabilidad disponibles para el período seleccionado.
        </Alert>
      )}
    </div>
  );
};

export default RentabilidadProductos;