import React from 'react';
import { Outlet, NavLink } from 'react-router-dom';
import { Container, Row, Col, Nav, Card } from 'react-bootstrap';
import { FaChartLine, FaBalanceScale, FaBoxes, FaCalendarAlt } from 'react-icons/fa';

const AnalisisLayout = () => {
  return (
    <Container fluid className="py-3">
      <h2 className="mb-4">Análisis Avanzado</h2>
      
      <Row>
        <Col md={3} lg={2} className="mb-4">
          <Card className="shadow-sm">
            <Card.Body className="p-0">
              <Nav className="flex-column">
                <Nav.Link as={NavLink} to="/analisis" end className="py-3 px-3 border-bottom">
                  <FaChartLine className="me-2" /> Resumen de Ventas
                </Nav.Link>
                <Nav.Link as={NavLink} to="/analisis/rentabilidad" className="py-3 px-3 border-bottom">
                  <FaBalanceScale className="me-2" /> Rentabilidad de Productos
                </Nav.Link>
                <Nav.Link as={NavLink} to="/analisis/cuadre-diario" className="py-3 px-3 border-bottom">
                  <FaCalendarAlt className="me-2" /> Cuadre Diario
                </Nav.Link>
                <Nav.Link as={NavLink} to="/analisis/inventario-proveedores" className="py-3 px-3">
                  <FaBoxes className="me-2" /> Inventario y Proveedores
                </Nav.Link>
              </Nav>
            </Card.Body>
          </Card>
        </Col>
        
        <Col md={9} lg={10}>
          <Card className="shadow-sm">
            <Card.Body>
              <Outlet />
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default AnalisisLayout;