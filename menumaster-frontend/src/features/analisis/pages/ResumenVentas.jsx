import React, { useState, useEffect } from 'react';
import { Row, Col, Card, Form, Button, Table, Alert, Spinner } from 'react-bootstrap';
import { getResumenVentas } from '@/features/analisis/services/analisisService';
import { Bar } from 'react-chartjs-2';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const ResumenVentas = () => {
  const [fechaInicio, setFechaInicio] = useState('');
  const [fechaFin, setFechaFin] = useState('');
  const [resumen, setResumen] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Establecer fechas predeterminadas (último mes)
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth() - 1, hoy.getDate());
    
    setFechaInicio(inicioMes.toISOString().split('T')[0]);
    setFechaFin(hoy.toISOString().split('T')[0]);
    
    cargarResumen(inicioMes.toISOString().split('T')[0], hoy.toISOString().split('T')[0]);
  }, []);

  const cargarResumen = async (inicio, fin) => {
    try {
      setLoading(true);
      setError(null);
      const response = await getResumenVentas(inicio, fin);
      if (response.status === 'success') {
        setResumen(response.data);
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
    cargarResumen(fechaInicio, fechaFin);
  };

  // Preparar datos para el gráfico
  const prepararDatosGrafico = () => {
    if (!resumen || !resumen.resumen_diario) return null;

    const labels = resumen.resumen_diario.map(item => item.fecha);
    const ventasData = resumen.resumen_diario.map(item => parseFloat(item.total_ventas));
    const costosData = resumen.resumen_diario.map(item => parseFloat(item.total_costos));
    const rentabilidadData = resumen.resumen_diario.map(item => parseFloat(item.rentabilidad));

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

  return (
    <div>
      <h3 className="mb-4">Resumen de Ventas</h3>
      
      <Form onSubmit={handleSubmit} className="mb-4">
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

      {!loading && !error && resumen && (
        <>
          <Row className="mb-4">
            <Col md={3}>
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Total Pedidos</h6>
                  <h3>{resumen.totales.total_pedidos}</h3>
                </Card.Body>
              </Card>
            </Col>
            <Col md={3}>
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Total Ventas</h6>
                  <h3>{formatearMoneda(resumen.totales.total_ventas)}</h3>
                </Card.Body>
              </Card>
            </Col>
            <Col md={3}>
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Total Costos</h6>
                  <h3>{formatearMoneda(resumen.totales.total_costos)}</h3>
                </Card.Body>
              </Card>
            </Col>
            <Col md={3}>
              <Card className="text-center h-100 shadow-sm">
                <Card.Body>
                  <h6 className="text-muted">Rentabilidad</h6>
                  <h3>{formatearMoneda(resumen.totales.total_rentabilidad)}</h3>
                  <span className="badge bg-info">
                    {resumen.totales.porcentaje_rentabilidad.toFixed(2)}%
                  </span>
                </Card.Body>
              </Card>
            </Col>
          </Row>

          <Card className="mb-4 shadow-sm">
            <Card.Body>
              <h5 className="mb-3">Evolución de Ventas y Costos</h5>
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
                    }
                  }}
                />
              )}
            </Card.Body>
          </Card>

          <Card className="shadow-sm">
            <Card.Body>
              <h5 className="mb-3">Detalle Diario</h5>
              <div className="table-responsive">
                <Table striped hover>
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Pedidos</th>
                      <th>Ventas</th>
                      <th>Costos</th>
                      <th>Rentabilidad</th>
                      <th>% Rentabilidad</th>
                    </tr>
                  </thead>
                  <tbody>
                    {resumen.resumen_diario.map((dia, index) => (
                      <tr key={index}>
                        <td>{new Date(dia.fecha).toLocaleDateString()}</td>
                        <td>{dia.total_pedidos}</td>
                        <td>{formatearMoneda(dia.total_ventas)}</td>
                        <td>{formatearMoneda(dia.total_costos)}</td>
                        <td>{formatearMoneda(dia.rentabilidad)}</td>
                        <td>
                          {parseFloat(dia.total_ventas) > 0 
                            ? (parseFloat(dia.rentabilidad) / parseFloat(dia.total_ventas) * 100).toFixed(2) 
                            : 0}%
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
              </div>
            </Card.Body>
          </Card>
        </>
      )}
    </div>
  );
};

export default ResumenVentas;