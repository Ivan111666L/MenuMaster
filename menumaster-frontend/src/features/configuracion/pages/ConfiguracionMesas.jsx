import React, { useEffect, useState, useCallback } from 'react';
import mesaService from '@/features/mesas/services/mesaService';
import { ESTADOS_MESA } from '@/utils/constant';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/configuracion.css'; // Asegúrate de tener este archivo de estilos

const estadoInicialNuevaMesa = {
  numero: '',
  capacidad: '',
  ubicacion: '',
  estado_nombre: ESTADOS_MESA.DISPONIBLE,
};

function ConfiguracionMesas() {
  const [mesas, setMesas] = useState([]);
  const [busqueda, setBusqueda] = useState('');
  const [nuevaMesa, setNuevaMesa] = useState(estadoInicialNuevaMesa);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false); // Estado para el botón de crear

  // Usamos useCallback para evitar que la función se re-cree innecesariamente
  const cargarMesas = useCallback(async () => {
    try {
      // No seteamos isLoading a true aquí para permitir recargas silenciosas
      const data = await mesaService.getMesas();
      setMesas(Array.isArray(data) ? data : []);
    } catch (err) {
      setError('Error al cargar las mesas.');
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    cargarMesas();
  }, [cargarMesas]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNuevaMesa(prev => ({ ...prev, [name]: value }));
  };

  const crearMesa = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      // Crear mesa (el backend establece estado 'disponible' por defecto)
      const creada = await mesaService.createMesa({
        numero: nuevaMesa.numero,
        capacidad: nuevaMesa.capacidad,
        ubicacion: nuevaMesa.ubicacion,
      });

      let mesaFinal = creada;
      // Si el usuario seleccionó un estado distinto al disponible, actualizamos inmediatamente
      if (nuevaMesa.estado_nombre !== ESTADOS_MESA.DISPONIBLE && creada?.id) {
        try {
          mesaFinal = await mesaService.updateMesa(creada.id, { estado_nombre: nuevaMesa.estado_nombre });
        } catch (updateErr) {
          console.warn('No se pudo establecer el estado inicial seleccionado. Usando disponible.', updateErr);
        }
      }

      // Actualización optimista: agregamos la mesa a la lista local sin bloquear el handler
      setMesas(prev => Array.isArray(prev) ? [...prev, mesaFinal] : [mesaFinal]);
      setNuevaMesa(estadoInicialNuevaMesa);

      // Refresco en segundo plano para sincronizar con backend y otras vistas
      window.dispatchEvent(new Event('mesas:update'));
      setTimeout(() => {
        cargarMesas();
      }, 0);
    } catch (err) {
      alert('Error al crear la mesa: ' + (err.response?.data?.error || err.message));
    } finally {
      setIsSubmitting(false);
    }
  };
  
  const cambiarEstado = async (id, nuevoEstado) => {
    const mesasOriginales = [...mesas];
    // Actualización optimista
    setMesas(prev => prev.map(m => m.id === id ? { ...m, estado: nuevoEstado } : m));
    
    try {
      await mesaService.updateMesa(id, { estado_nombre: nuevoEstado });
      window.dispatchEvent(new Event('mesas:update'));
    } catch (err) {
        alert('Error al cambiar el estado.');
        setMesas(mesasOriginales); // Revertir en caso de error
    }
  };

  const eliminarMesa = async (id) => {
    if (window.confirm('¿Estás seguro de que quieres eliminar esta mesa?')) {
      try {
        await mesaService.deleteMesa(id);
        setMesas(prev => prev.filter(m => m.id !== id)); // Actualización optimista
        window.dispatchEvent(new Event('mesas:update'));
      } catch (err) {
        alert('Error al eliminar la mesa.');
        cargarMesas(); // Recargar para re-sincronizar
      }
    }
  };

  const mesasFiltradas = mesas.filter(m =>
    `${m.numero} ${m.ubicacion}`.toLowerCase().includes(busqueda.toLowerCase())
  );
  
  if (isLoading) return <Spinner />;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="gestion-mesas-container">
      <h1 className="gestion-mesas-title">Gestión de Mesas</h1>

      <form onSubmit={crearMesa} className="crear-mesa-form">
        {/* CORRECCIÓN: Se usa el 'id' en los Input para vincular con el label implícito */}
        <Input id="numero" name="numero" placeholder="Número (ej: M5)" value={nuevaMesa.numero} onChange={handleInputChange} required />
        <Input id="capacidad" name="capacidad" type="number" placeholder="Capacidad" value={nuevaMesa.capacidad} onChange={handleInputChange} required />
        <Input id="ubicacion" name="ubicacion" placeholder="Ubicación" value={nuevaMesa.ubicacion} onChange={handleInputChange} />
        {/* Selector de estado inicial usando constantes centralizadas */}
        <select
          id="estado_nombre"
          name="estado_nombre"
          className="form-input"
          value={nuevaMesa.estado_nombre}
          onChange={handleInputChange}
        >
          <option value={ESTADOS_MESA.DISPONIBLE}>Disponible</option>
          <option value={ESTADOS_MESA.OCUPADA}>Ocupada</option>
          <option value={ESTADOS_MESA.RESERVADA}>Reservada</option>
        </select>
        <Button type="submit" variant="primary" disabled={isSubmitting}>
          {isSubmitting ? <Spinner /> : 'Crear Mesa'}
        </Button>
      </form>

      <Input
        id="busqueda"
        name="busqueda"
        placeholder="Buscar por número o ubicación..."
        value={busqueda}
        onChange={(e) => setBusqueda(e.target.value)}
        className="buscador-mesas"
      />

      <div className="table-wrapper">
        <table className="mesas-table">
          <thead>
            <tr>
              <th>Número</th>
              <th>Capacidad</th>
              <th>Ubicación</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {mesasFiltradas.map((m) => (
              <tr key={m.id}>
                <td>{m.numero}</td>
                <td>{m.capacidad}</td>
                <td>{m.ubicacion}</td>
                <td>
                  <select className="form-input" value={m.estado} onChange={(e) => cambiarEstado(m.id, e.target.value)}>
                    <option value={ESTADOS_MESA.DISPONIBLE}>Disponible</option>
                    <option value={ESTADOS_MESA.OCUPADA}>Ocupada</option>
                    <option value={ESTADOS_MESA.RESERVADA}>Reservada</option>
                  </select>
                </td>
                <td>
                  <Button variant="danger" onClick={() => eliminarMesa(m.id)}>
                    Eliminar
                  </Button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default ConfiguracionMesas;