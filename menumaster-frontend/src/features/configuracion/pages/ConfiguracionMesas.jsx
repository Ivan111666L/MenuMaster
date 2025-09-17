import React, { useEffect, useState, useCallback } from 'react';
import mesaService from '@/features/mesas/services/mesaService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import './ConfiguracionMesas.css'; // Asegúrate de tener este archivo de estilos

const estadoInicialNuevaMesa = {
  numero: '',
  capacidad: '',
  ubicacion: '',
  estado_nombre: 'disponible',
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
      await mesaService.createMesa(nuevaMesa);
      setNuevaMesa(estadoInicialNuevaMesa);
      await cargarMesas(); // Recargar la lista
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
        <Button type="submit" variant="primary" disabled={isSubmitting}>
          {isSubmitting ? <Spinner /> : 'Crear Mesa'}
        </Button>
      </form>

      <Input
        id="busqueda"
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
                    <option value="disponible">Disponible</option>
                    <option value="ocupada">Ocupada</option>
                    <option value="reservada">Reservada</option>
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