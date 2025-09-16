import React, { useEffect, useState } from 'react';
import mesaService from '@/features/mesas/services/mesaService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/configuracion.css';

const estadoInicialNuevaMesa = {
  numero: '',
  capacidad: '',
  ubicacion: '',
  estado_nombre: 'disponible', // Usamos el nombre del estado para la creación
};

function ConfiguracionMesas() {
  const [mesas, setMesas] = useState([]);
  const [busqueda, setBusqueda] = useState('');
  const [nuevaMesa, setNuevaMesa] = useState(estadoInicialNuevaMesa);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  const cargarMesas = async () => {
    try {
      setIsLoading(true);
      const data = await mesaService.getMesas();
      setMesas(Array.isArray(data) ? data : []);
    } catch (err) {
      setError('Error al cargar las mesas.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    cargarMesas();
  }, []);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNuevaMesa(prev => ({ ...prev, [name]: value }));
  };

  const crearMesa = async (e) => {
    e.preventDefault();
    try {
      await mesaService.createMesa(nuevaMesa);
      setNuevaMesa(estadoInicialNuevaMesa);
      cargarMesas(); // Recargar la lista
    } catch (err) {
      alert('Error al crear la mesa.');
    }
  };
  
  const cambiarEstado = async (id, nuevoEstado) => {
    try {
      await mesaService.updateMesa(id, { estado_nombre: nuevoEstado });
      // Actualizamos el estado local para una respuesta visual instantánea
      setMesas(prev => prev.map(m => m.id === id ? { ...m, estado: nuevoEstado } : m));
    } catch (err) {
        alert('Error al cambiar el estado.');
    }
  };

  const eliminarMesa = async (id) => {
    if (window.confirm('¿Estás seguro de que quieres eliminar esta mesa?')) {
      try {
        await mesaService.deleteMesa(id);
        cargarMesas(); // Recargar la lista
      } catch (err) {
        alert('Error al eliminar la mesa.');
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
        <Input name="numero" placeholder="Número (ej: M5)" value={nuevaMesa.numero} onChange={handleInputChange} required />
        <Input name="capacidad" type="number" placeholder="Capacidad" value={nuevaMesa.capacidad} onChange={handleInputChange} required />
        <Input name="ubicacion" placeholder="Ubicación" value={nuevaMesa.ubicacion} onChange={handleInputChange} />
        <Button type="submit" variant="primary">Crear Mesa</Button>
      </form>

      <Input
        placeholder="Buscar por número o ubicación..."
        value={busqueda}
        onChange={(e) => setBusqueda(e.target.value)}
        className="buscador-mesas"
      />

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
                <select value={m.estado} onChange={(e) => cambiarEstado(m.id, e.target.value)}>
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
  );
};

export default ConfiguracionMesas;