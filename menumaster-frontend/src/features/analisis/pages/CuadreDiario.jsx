import React, { useState, useEffect } from 'react';
// First install react-bootstrap:
// npm install react-bootstrap bootstrap

// Then add bootstrap CSS in your app's entry point (e.g. index.js or App.js):
// import 'bootstrap/dist/css/bootstrap.min.css';

import { Row, Col, Form, Button, Table, Alert, Spinner } from 'react-bootstrap';
import { getCuadresDiarios, crearOActualizarCuadreDiario, getProductosMasVendidos, getVentasPorDiaPorUsuario } from '@/features/analisis/services/analisisService';
import usuarioService from '@/features/usuarios/services/usuarioService';
import { getPagos, getMetodosPago } from '@/features/pagos/services/pagoService';
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
  const [resumenPagos, setResumenPagos] = useState([]);
  const [metodosPago, setMetodosPago] = useState([]);
  const [presetFecha, setPresetFecha] = useState('mes'); // hoy, semana, mes, personalizado
  const [metodosSeleccionados, setMetodosSeleccionados] = useState([]);
  const [usuarios, setUsuarios] = useState([]);
  const [usuariosSeleccionados, setUsuariosSeleccionados] = useState([]);
  const [umbralOutlier, setUmbralOutlier] = useState(2.5);
  const [horaInicio, setHoraInicio] = useState('');
  const [horaFin, setHoraFin] = useState('');
  const [diasPronostico, setDiasPronostico] = useState(7);
  const [zScoresPorDia, setZScoresPorDia] = useState([]);
  const [guardarPronosticoPorDefecto, setGuardarPronosticoPorDefecto] = useState(false);
  const [rolFiltroUsuarios, setRolFiltroUsuarios] = useState('todos');
  const [ventasUsuariosSinDatos, setVentasUsuariosSinDatos] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    // Establecer fechas predeterminadas (último mes)
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    setFechaInicio(inicioMes.toISOString().split('T')[0]);
    setFechaFin(hoy.toISOString().split('T')[0]);
    
    cargarCuadres(inicioMes.toISOString().split('T')[0], hoy.toISOString().split('T')[0]);
    // Cargar preferencia de horizonte de pronóstico desde almacenamiento local
    try {
      const pref = localStorage.getItem('preferencia_horizonte_pronostico');
      if (pref) {
        const val = parseInt(pref);
        if (!isNaN(val)) {
          setDiasPronostico(val);
        }
      }
      // Global desde configuraciones
      const cfgStr = localStorage.getItem('menumaster_configuraciones');
      if (cfgStr) {
        const cfg = JSON.parse(cfgStr);
        const g = parseInt(cfg?.sistema?.horizonte_pronostico_default);
        if (!isNaN(g)) {
          setDiasPronostico(g);
        }
      }
    } catch (e) {
      // Ignorar errores de localStorage
    }
  }, []);

  const cargarCuadres = async (inicio, fin) => {
    try {
      setLoading(true);
      setError(null);
      const [cuadresResp, topResp, pagosResp, metodosResp, usuariosResp] = await Promise.all([
        getCuadresDiarios(inicio, fin),
        getProductosMasVendidos(inicio, fin),
        getPagos(),
        getMetodosPago().catch(() => []),
        usuarioService.getUsuarios().catch(() => [])
      ]);

      if (cuadresResp.status === 'success') {
        const data = cuadresResp.data || [];
        setCuadres(data);

        // Preparar datos para gráfico de comparación Ventas vs Pagos vs Compras a Proveedores
        const labelKeys = data.map(item => new Date(item.fecha).toISOString().split('T')[0]);
        const labels = data.map(item => new Date(item.fecha).toLocaleDateString());
        const ventasData = data.map(item => parseFloat(item.total_ventas));
        const comprasData = data.map(item => parseFloat(item.total_compras_proveedores));

        // Procesar pagos en el rango
        const pagosAll = pagosResp?.length ? pagosResp : (pagosResp?.data?.data ?? pagosResp?.data ?? []);
        const enRango = (fechaStr) => {
          try {
            // Construir rango fecha-hora si se definieron horas para personalizado
            const hasHoras = presetFecha === 'personalizado' && (horaInicio || horaFin);
            const fi = hasHoras ? new Date(`${inicio}T${horaInicio || '00:00'}`) : new Date(inicio);
            const ff = hasHoras ? new Date(`${fin}T${horaFin || '23:59'}`) : new Date(fin);
            if (!hasHoras) {
              fi.setHours(0,0,0,0);
              ff.setHours(23,59,59,999);
            }
            const s = typeof fechaStr === 'string' ? fechaStr.replace(' ', 'T') : fechaStr;
            const f = new Date(s);
            return f >= fi && f <= ff;
          } catch { return false; }
        };
        let pagosFiltrados = (pagosAll || []).filter(p => enRango(p.fecha_pago));
        // Filtro por métodos seleccionados
        if (metodosSeleccionados && metodosSeleccionados.length > 0) {
          const setSel = new Set(metodosSeleccionados.map(v => parseInt(v)));
          pagosFiltrados = pagosFiltrados.filter(p => setSel.has(parseInt(p.metodo_pago_id)));
        }
        // Filtro por usuarios/meseros
        if (usuariosSeleccionados && usuariosSeleccionados.length > 0) {
          const setUsers = new Set(usuariosSeleccionados.map(v => parseInt(v)));
          pagosFiltrados = pagosFiltrados.filter(p => setUsers.has(parseInt(p.usuario_id)));
        }

        // Resumen por método de pago
        const metodos = Array.isArray(metodosResp) ? metodosResp : (metodosResp?.data?.data ?? []);
        setMetodosPago(metodos);
        const nombreMetodo = {};
        (metodos || []).forEach(m => { nombreMetodo[m.id] = m.nombre; });

        const resumen = {};
        pagosFiltrados.forEach(p => {
          const id = p.metodo_pago_id || 'desconocido';
          const monto = parseFloat(p.monto || 0);
          if (!resumen[id]) resumen[id] = { metodo_id: id, metodo_nombre: nombreMetodo[id] || `Método ${id}`, total: 0, cantidad: 0 };
          resumen[id].total += monto;
          resumen[id].cantidad += 1;
        });
        setResumenPagos(Object.values(resumen));

        // Pagos por día para gráfica
        const pagosPorDia = {};
        pagosFiltrados.forEach(p => {
          const key = new Date(p.fecha_pago).toISOString().split('T')[0];
          pagosPorDia[key] = (pagosPorDia[key] || 0) + parseFloat(p.monto || 0);
        });
        const pagosData = labelKeys.map(k => pagosPorDia[k] || 0);

        // Pronóstico como curva (ajuste lineal sobre ventas existentes)
        const computeLinearFitParams = (vals) => {
          const series = (vals || []).map((v, i) => ({ x: i, y: parseFloat(v || 0) }));
          if (series.length < 2) return { m: 0, b: 0, fit: [] };
          const n = series.length;
          const sumX = series.reduce((a, s) => a + s.x, 0);
          const sumY = series.reduce((a, s) => a + s.y, 0);
          const sumXY = series.reduce((a, s) => a + s.x * s.y, 0);
          const sumXX = series.reduce((a, s) => a + s.x * s.x, 0);
          const denom = (n * sumXX - sumX * sumX);
          if (denom === 0) return { m: 0, b: 0, fit: [] };
          const m = (n * sumXY - sumX * sumY) / denom;
          const b = (sumY - m * sumX) / n;
          const fit = series.map(s => Math.max(0, m * s.x + b));
          return { m, b, fit };
        };
        const { m, b, fit } = computeLinearFitParams(ventasData);

        // Detección de outliers y cálculo de z-score (aplicado a ventas)
        const computeZScores = (vals) => {
          const arr = (vals || []).map(v => parseFloat(v || 0));
          if (arr.length < 3) return [];
          const mean = arr.reduce((a, v) => a + v, 0) / arr.length;
          const variance = arr.reduce((a, v) => a + Math.pow(v - mean, 2), 0) / arr.length;
          const std = Math.sqrt(variance) || 0;
          if (std === 0) return arr.map(() => 0);
          return arr.map(v => (v - mean) / std);
        };
        const zScores = computeZScores(ventasData);
        const outIdxSet = new Set(zScores.map((z, i) => (Math.abs(z) >= (parseFloat(umbralOutlier) || 2.5)) ? i : null).filter(v => v !== null));
        const ventasBg = ventasData.map((_, i) => outIdxSet.has(i) ? 'rgba(255, 99, 132, 0.6)' : 'rgba(54, 162, 235, 0.5)');
        const ventasBorder = ventasData.map((_, i) => outIdxSet.has(i) ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)');

        // Extender etiquetas y datos para pronóstico futuro
        let labelsFinal = [...labels];
        let labelKeysFinal = [...labelKeys];
        const nOriginal = ventasData.length;
        if (diasPronostico > 0 && labelKeysFinal.length > 0) {
          const lastDate = new Date(labelKeysFinal[labelKeysFinal.length - 1]);
          for (let i = 1; i <= diasPronostico; i++) {
            const next = new Date(lastDate);
            next.setDate(lastDate.getDate() + i);
            labelKeysFinal.push(next.toISOString().split('T')[0]);
            labelsFinal.push(next.toLocaleDateString());
          }
        }

        const ventasDataExt = [...ventasData, ...Array(diasPronostico).fill(null)];
        const pagosDataExt = [...pagosData, ...Array(diasPronostico).fill(null)];
        const comprasDataExt = [...comprasData, ...Array(diasPronostico).fill(null)];
        const fitExt = [
          ...fit,
          ...Array.from({ length: diasPronostico }, (_, idx) => {
            const x = nOriginal + idx;
            return Math.max(0, m * x + b);
          })
        ];

        const zScoresExt = [...zScores, ...Array(diasPronostico).fill(null)];
        setZScoresPorDia(zScoresExt);

        // Si hay usuarios seleccionados, consumir ventas filtradas por mesero y agregar dataset
        let ventasUsuariosDataExt = null;
        try {
          if (usuariosSeleccionados && usuariosSeleccionados.length > 0) {
            const ventasUsuResp = await getVentasPorDiaPorUsuario(inicio, fin, usuariosSeleccionados);
            const ventasUsuArr = ventasUsuResp?.data ?? ventasUsuResp ?? [];
            const mapaVentasUsu = {};
            (Array.isArray(ventasUsuArr) ? ventasUsuArr : []).forEach(v => {
              const key = new Date(v.fecha).toISOString().split('T')[0];
              const total = parseFloat(v.total_ventas ?? v.total ?? 0);
              mapaVentasUsu[key] = total;
            });
            const ventasUsuData = labelKeys.map(k => mapaVentasUsu[k] ?? 0);
            ventasUsuariosDataExt = [...ventasUsuData, ...Array(diasPronostico).fill(null)];
            // Validación visual: dataset vacío
            const sumaVentasUsu = (ventasUsuData || []).reduce((acc, v) => acc + (parseFloat(v) || 0), 0);
            setVentasUsuariosSinDatos((sumaVentasUsu <= 0));
          } else {
            setVentasUsuariosSinDatos(false);
          }
        } catch (e) {
          console.warn('No se pudieron cargar ventas filtradas por usuario', e);
          setVentasUsuariosSinDatos(false);
        }

        const datasets = [
          {
            label: 'Ventas',
            data: ventasDataExt,
            backgroundColor: ventasBg,
            borderColor: ventasBorder,
            borderWidth: 1
          },
        ];

        if (ventasUsuariosDataExt) {
          datasets.push({
            label: 'Ventas (meseros seleccionados)',
            data: ventasUsuariosDataExt,
            backgroundColor: 'rgba(255, 206, 86, 0.5)',
            borderColor: 'rgba(255, 206, 86, 1)',
            borderWidth: 1
          });
        }

        datasets.push(
          {
            label: 'Pagos',
            data: pagosDataExt,
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          },
          {
            label: 'Compras a Proveedores',
            data: comprasDataExt,
            backgroundColor: 'rgba(255, 159, 64, 0.5)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
          },
          {
            label: 'Pronóstico (ajuste)',
            data: fitExt,
            type: 'line',
            borderColor: 'rgba(153, 102, 255, 1)',
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            tension: 0.3,
            pointRadius: 0,
            borderWidth: 2
          }
        );

        setComparacion({ labels: labelsFinal, datasets });
      } else {
        setError(cuadresResp.message || 'Error al cargar los cuadres diarios');
      }

      // Procesar top productos (acepta formato {status:'success'} o {success:true})
      if ((topResp && topResp.status === 'success') || (topResp && topResp.success === true)) {
        const productos = topResp.data?.productos || topResp.data || [];
        setTopProductos(productos);
      }

      // Usuarios para filtros
      setUsuarios(Array.isArray(usuariosResp) ? usuariosResp : []);
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

  const exportResumenPagosCSV = () => {
    try {
      const headers = ['metodo_id','metodo_nombre','cantidad','total'];
      const rows = (resumenPagos || []).map(r => [
        r.metodo_id, r.metodo_nombre, r.cantidad, r.total
      ]);
      const csv = [headers.join(','), ...rows.map(row => row.join(','))].join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `resumen_pagos_${fechaInicio}_a_${fechaFin}.csv`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    } catch (e) {
      console.error('Error al exportar CSV', e);
    }
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

      {/* Filtros avanzados */}
      <Form className="mb-4">
        <Row>
          <Col md={4}>
            <Form.Group className="mb-3">
              <Form.Label>Rango rápido</Form.Label>
              <Form.Select value={presetFecha} onChange={(e) => {
                const val = e.target.value;
                setPresetFecha(val);
                const hoy = new Date();
                let ini = new Date(hoy);
                if (val === 'hoy') {
                  ini = new Date(hoy);
                } else if (val === 'semana') {
                  ini.setDate(hoy.getDate() - 6);
                } else if (val === 'mes') {
                  ini = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                }
                const fInicio = ini.toISOString().split('T')[0];
                const fFin = hoy.toISOString().split('T')[0];
                setFechaInicio(fInicio);
                setFechaFin(fFin);
                cargarCuadres(fInicio, fFin);
              }}>
                <option value="hoy">Hoy</option>
                <option value="semana">Últimos 7 días</option>
                <option value="mes">Mes actual</option>
                <option value="personalizado">Personalizado</option>
              </Form.Select>
            </Form.Group>
          </Col>
          <Col md={4}>
            <Form.Group className="mb-3">
              <Form.Label>Métodos de pago</Form.Label>
              <Form.Select multiple value={metodosSeleccionados} onChange={(e) => {
                const opts = Array.from(e.target.selectedOptions).map(o => o.value);
                setMetodosSeleccionados(opts);
                // No recargamos automáticamente; el próximo submit aplicará el filtro
              }}>
                {(metodosPago || []).map(m => (
                  <option key={m.id} value={m.id}>{m.nombre}</option>
                ))}
              </Form.Select>
              <div className="form-text">Usa Ctrl/Cmd para selección múltiple.</div>
            </Form.Group>
          </Col>
          <Col md={4}>
              <Form.Group className="mb-3">
              <Form.Label>Meseros/Usuarios</Form.Label>
              <Form.Select className="mb-2" value={rolFiltroUsuarios} onChange={(e) => setRolFiltroUsuarios(e.target.value)}>
                <option value="todos">Todos los roles</option>
                {Array.from(new Set((usuarios || []).map(u => (u.rol || '').toLowerCase()).filter(Boolean))).map(rol => (
                  <option key={rol} value={rol}>{rol.charAt(0).toUpperCase() + rol.slice(1)}</option>
                ))}
              </Form.Select>
              <Form.Select multiple value={usuariosSeleccionados} onChange={(e) => {
                const opts = Array.from(e.target.selectedOptions).map(o => o.value);
                setUsuariosSeleccionados(opts);
              }}>
                {(((usuarios || []).filter(u => {
                  const r = (u.rol || '').toLowerCase();
                  return rolFiltroUsuarios === 'todos' ? true : r === rolFiltroUsuarios;
                })) || []).map(u => {
                  const label = (u?.nombre && u?.email)
                    ? `${u.nombre} (${u.email})`
                    : (u?.nombre || u?.email || `Usuario ${u.id}`);
                  return (
                    <option key={u.id} value={u.id}>{label}</option>
                  );
                })}
              </Form.Select>
              <div className="form-text">Filtra pagos por usuario/mesero.</div>
              {(!loading && (usuarios || []).length === 0) && (
                <div className="form-text text-warning">
                  No se obtuvieron usuarios. Puede deberse a permisos insuficientes (403) o token vencido (401). Intenta reautenticarse o solicita acceso.
                </div>
              )}
              {(!loading && usuariosSeleccionados.length > 0 && ventasUsuariosSinDatos) && (
                <div className="form-text text-danger">
                  No hay ventas para los meseros seleccionados en el período.
                </div>
              )}
            </Form.Group>
          </Col>
        </Row>
        {presetFecha === 'personalizado' && (
          <Row>
            <Col md={6}>
              <Form.Group className="mb-3">
                <Form.Label>Hora inicio (opcional)</Form.Label>
                <Form.Control type="time" value={horaInicio} onChange={(e) => setHoraInicio(e.target.value)} />
              </Form.Group>
            </Col>
            <Col md={6}>
              <Form.Group className="mb-3">
                <Form.Label>Hora fin (opcional)</Form.Label>
                <Form.Control type="time" value={horaFin} onChange={(e) => setHoraFin(e.target.value)} />
              </Form.Group>
            </Col>
          </Row>
        )}
        <Row>
          <Col md={12}>
            <Form.Group className="mb-3">
              <Form.Label>Umbral Outliers (Z-score)</Form.Label>
              <Form.Range min={1} max={4} step={0.1} value={umbralOutlier} onChange={(e) => {
                setUmbralOutlier(e.target.value);
                // Recalcular con el nuevo umbral
                cargarCuadres(fechaInicio, fechaFin);
              }} />
              <div className="form-text">Actual: {parseFloat(umbralOutlier).toFixed(1)}</div>
            </Form.Group>
          </Col>
          <Col md={4}>
            <Form.Group className="mb-3">
              <Form.Label>Días de pronóstico (futuro)</Form.Label>
              <Form.Range min={0} max={14} step={1} value={diasPronostico} onChange={(e) => {
                const val = parseInt(e.target.value);
                setDiasPronostico(val);
                if (guardarPronosticoPorDefecto) {
                  try {
                    localStorage.setItem('preferencia_horizonte_pronostico', String(val));
                  } catch {}
                }
                cargarCuadres(fechaInicio, fechaFin);
              }} />
              <div className="form-text">Extiende la línea de pronóstico {diasPronostico} días.</div>
              <Form.Check
                className="mt-2"
                type="checkbox"
                label="Guardar como predeterminado"
                checked={guardarPronosticoPorDefecto}
                onChange={(e) => setGuardarPronosticoPorDefecto(e.target.checked)}
              />
              <div className="form-text">
                Predeterminado actual: {(() => { try { return parseInt(localStorage.getItem('preferencia_horizonte_pronostico')) || 0; } catch { return 0; } })()} días.
              </div>
            </Form.Group>
          </Col>
        </Row>
      </Form>

      {!loading && !error && resumenPagos && resumenPagos.length > 0 && (
        <div className="section-block">
          <div className="d-flex justify-content-between align-items-center mb-3">
            <h5 className="m-0">Resumen de Pagos por Método</h5>
            <Button variant="outline-primary" size="sm" onClick={exportResumenPagosCSV}>Exportar CSV</Button>
          </div>
          <div className="table-responsive analisis-table">
            <Table striped hover className="mb-0">
              <thead>
                <tr>
                  <th>Método</th>
                  <th>Cantidad de pagos</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                {resumenPagos.map((r, idx) => (
                  <tr key={idx}>
                    <td>{r.metodo_nombre}</td>
                    <td>{r.cantidad}</td>
                    <td>{formatearMoneda(r.total)}</td>
                  </tr>
                ))}
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
          <h5 className="mb-3">Comparación: Ventas vs Pagos vs Compras a Proveedores</h5>
          <Bar 
            data={comparacion}
            options={{
              responsive: true,
              plugins: {
                legend: { position: 'top' },
                title: { display: false },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      const label = context.dataset.label || '';
                      const value = context.parsed?.y;
                      if (label === 'Ventas') {
                        const z = (zScoresPorDia || [])[context.dataIndex];
                        if (z !== null && z !== undefined) {
                          const zStr = Number(z).toFixed(2);
                          return `${label}: ${value ?? '-'} (z-score: ${zStr})`;
                        }
                      }
                      return `${label}: ${value ?? '-'}`;
                    }
                  }
                }
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