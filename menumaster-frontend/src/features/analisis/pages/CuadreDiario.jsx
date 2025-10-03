import React, { useState, useEffect } from 'react';
// First install react-bootstrap:
// npm install react-bootstrap bootstrap

// Then add bootstrap CSS in your app's entry point (e.g. index.js or App.js):
// import 'bootstrap/dist/css/bootstrap.min.css';

import { Row, Col, Form, Button, Table, Alert, Spinner } from 'react-bootstrap';
import { getCuadresDiarios, crearOActualizarCuadreDiario, getProductosMasVendidos } from '@/features/analisis/services/analisisService';
import { useNavigate } from 'react-router-dom';
import { FaArrowLeft } from 'react-icons/fa';
import { Bar } from 'react-chartjs-2';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const CuadreDiario = () => {
  const [fechaInicio, setFechaInicio] = useState('');
  const [fechaFin, setFechaFin] = useState('');
  const [cuadres, setCuadres] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [formData, setFormData] = useState({
    fecha: '',
    total_compras_proveedores: '',
    notas: ''
  });
  const [successMessage, setSuccessMessage] = useState('');
  const [topProductos, setTopProductos] = useState([]);
  const [comparacion, setComparacion] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    // Establecer fechas predeterminadas (último mes)
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    setFechaInicio(inicioMes.toISOString().split('T')[0]);
    setFechaFin(hoy.toISOString().split('T')[0]);
    
    cargarCuadres(inicioMes.toISOString().split('T')[0], hoy.toISOString().split('T')[0]);
  }, []);

  const cargarCuadres = async (inicio, fin) => {
    try {
      setLoading(true);
      setError(null);
      const [cuadresResp, topResp] = await Promise.all([
        getCuadresDiarios(inicio, fin),
        getProductosMasVendidos(inicio, fin)
      ]);

      if (cuadresResp.status === 'success') {
        const data = cuadresResp.data || [];
        setCuadres(data);

        // Preparar datos para gráfico de comparación Ventas vs Compras a Proveedores
        const labels = data.map(item => new Date(item.fecha).toLocaleDateString());
        const ventasData = data.map(item => parseFloat(item.total_ventas));
        const comprasData = data.map(item => parseFloat(item.total_compras_proveedores));
        setComparacion({
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
              label: 'Compras a Proveedores',
              data: comprasData,
              backgroundColor: 'rgba(255, 159, 64, 0.5)',
              borderColor: 'rgba(255, 159, 64, 1)',
              borderWidth: 1
            }
          ]
        });
      } else {
        setError(cuadresResp.message || 'Error al cargar los cuadres diarios');
      }

      // Procesar top productos (acepta formato {status:'success'} o {success:true})
      if ((topResp && topResp.status === 'success') || (topResp && topResp.success === true)) {
        const productos = topResp.data?.productos || topResp.data || [];
        setTopProductos(productos);
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
    cargarCuadres(fechaInicio, fechaFin);
  };

  const handleNuevoCuadre = () => {
    const hoy = new Date().toISOString().split('T')[0];
    setFormData({
      fecha: hoy,
      total_compras_proveedores: '',
      notas: ''
    });
    setShowModal(true);
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
  };

  const handleGuardarCuadre = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      const response = await crearOActualizarCuadreDiario(formData);
      if (response.status === 'success') {
        setSuccessMessage(response.message);
        setShowModal(false);
        // Recargar los cuadres
        cargarCuadres(fechaInicio, fechaFin);
      } else {
        setError(response.message || 'Error al guardar el cuadre diario');
      }
    } catch (err) {
      setError('Error de conexión al servidor');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const formatearMoneda = (valor) => {
    return parseFloat(valor).toLocaleString('es-CO', {
      style: 'currency',
      currency: 'COP',
      minimumFractionDigits: 0
    });
  };

  return (
    <div className="analisis-section">
      <div className="d-flex justify-content-between align-items-center mb-4 analisis-toolbar">
        <h3 className="m-0">Cuadre Diario</h3>
        <div className="d-flex gap-2">
          <Button variant="outline-secondary" onClick={() => navigate('/analisis')}>
            <FaArrowLeft className="me-2" /> Volver a Análisis
          </Button>
          <Button variant="success" onClick={handleNuevoCuadre}>
            Nuevo Cuadre
          </Button>
        </div>
      </div>
      
      {successMessage && (
        <Alert variant="success" onClose={() => setSuccessMessage('')} dismissible>
          {successMessage}
        </Alert>
      )}
      
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

      {!loading && !error && (
        <div className="section-block">
          <h5 className="mb-3">Resumen Diario</h5>
          <div className="table-responsive analisis-table">
            <Table striped hover className="mb-0">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Ventas</th>
                  <th>Costos Productos</th>
                  <th>Compras Proveedores</th>
                  <th>Rentabilidad</th>
                  <th>% Rentabilidad</th>
                  <th>Notas</th>
                </tr>
              </thead>
              <tbody>
                {cuadres.length > 0 ? (
                  cuadres.map((cuadre, index) => {
                      const rentabilidad = parseFloat(cuadre.total_ventas) - 
                                          parseFloat(cuadre.total_costos) - 
                                          parseFloat(cuadre.total_compras_proveedores);
                      const porcentajeRentabilidad = parseFloat(cuadre.total_ventas) > 0 
                        ? (rentabilidad / parseFloat(cuadre.total_ventas) * 100) 
                        : 0;
                      
                      return (
                        <tr key={index}>
                          <td>{new Date(cuadre.fecha).toLocaleDateString()}</td>
                          <td>{formatearMoneda(cuadre.total_ventas)}</td>
                          <td>{formatearMoneda(cuadre.total_costos)}</td>
                          <td>{formatearMoneda(cuadre.total_compras_proveedores)}</td>
                          <td>{formatearMoneda(rentabilidad)}</td>
                          <td>{porcentajeRentabilidad.toFixed(2)}%</td>
                          <td>{cuadre.notas}</td>
                        </tr>
                      );
                    })
                  ) : (
                    <tr>
                      <td colSpan="7" className="text-center">No hay cuadres diarios para el período seleccionado</td>
                    </tr>
                  )}
              </tbody>
            </Table>
          </div>
        </div>
      )}

      {!loading && !error && topProductos && topProductos.length > 0 && (
        <div className="section-block">
          <h5 className="mb-3">Productos más vendidos</h5>
          <div className="table-responsive analisis-table">
            <Table striped hover className="mb-0">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad Vendida</th>
                  <th>Ventas</th>
                </tr>
              </thead>
              <tbody>
                {topProductos.map((p, idx) => (
                  <tr key={idx}>
                    <td>{p.producto_nombre || p.nombre || 'Producto'}</td>
                    <td>{p.cantidad_total || p.cantidad || 0}</td>
                    <td>{formatearMoneda(p.ventas_totales || p.total || 0)}</td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>
        </div>
      )}

      {!loading && !error && comparacion && (
        <div className="section-block">
          <h5 className="mb-3">Comparación: Ventas vs Compras a Proveedores</h5>
          <Bar 
            data={comparacion}
            options={{
              responsive: true,
              plugins: {
                legend: { position: 'top' },
                title: { display: false }
              },
              scales: {
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
              }
            }}
          />
        </div>
      )}

      {/* Formulario inline para nuevo cuadre dentro del contenido principal */}
      {showModal && (
        <div className="section-block">
          <h5 className="mb-3">Nuevo Cuadre Diario</h5>
          <Form onSubmit={handleGuardarCuadre}>
            <Form.Group className="mb-3">
              <Form.Label>Fecha</Form.Label>
              <Form.Control
                type="date"
                name="fecha"
                value={formData.fecha}
                onChange={handleFormChange}
                required
              />
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Total Compras a Proveedores</Form.Label>
              <Form.Control
                type="number"
                name="total_compras_proveedores"
                value={formData.total_compras_proveedores}
                onChange={handleFormChange}
                required
                min="0"
                step="0.01"
              />
              <Form.Text className="text-muted">
                Ingrese el monto total de compras a proveedores para esta fecha
              </Form.Text>
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Notas</Form.Label>
              <Form.Control
                as="textarea"
                name="notas"
                value={formData.notas}
                onChange={handleFormChange}
                rows={3}
              />
            </Form.Group>
            <div className="d-flex justify-content-end">
              <Button variant="secondary" className="me-2" onClick={() => setShowModal(false)}>
                Cancelar
              </Button>
              <Button variant="primary" type="submit">
                Guardar
              </Button>
            </div>
          </Form>
        </div>
      )}
    </div>
  );
};

export default CuadreDiario;