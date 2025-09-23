import React, { useState, useEffect } from 'react';
// First install react-bootstrap:
// npm install react-bootstrap bootstrap

// Then add bootstrap CSS in your app's entry point (e.g. index.js or App.js):
// import 'bootstrap/dist/css/bootstrap.min.css';

import { Row, Col, Card, Form, Button, Table, Alert, Spinner, Modal } from 'react-bootstrap';
import { getCuadresDiarios, crearOActualizarCuadreDiario } from '@/features/analisis/services/analisisService';

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
      const response = await getCuadresDiarios(inicio, fin);
      if (response.status === 'success') {
        setCuadres(response.data);
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
    <div>
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h3>Cuadre Diario</h3>
        <Button variant="success" onClick={handleNuevoCuadre}>
          Nuevo Cuadre
        </Button>
      </div>
      
      {successMessage && (
        <Alert variant="success" onClose={() => setSuccessMessage('')} dismissible>
          {successMessage}
        </Alert>
      )}
      
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

      {!loading && !error && (
        <Card className="shadow-sm">
          <Card.Body>
            <div className="table-responsive">
              <Table striped hover>
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
          </Card.Body>
        </Card>
      )}

      {/* Modal para nuevo cuadre */}
      <Modal show={showModal} onHide={() => setShowModal(false)}>
        <Modal.Header closeButton>
          <Modal.Title>Nuevo Cuadre Diario</Modal.Title>
        </Modal.Header>
        <Modal.Body>
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
        </Modal.Body>
      </Modal>
    </div>
  );
};

export default CuadreDiario;