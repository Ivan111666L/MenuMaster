import React, { useEffect, useState } from 'react';
import { Link, Outlet } from 'react-router-dom';

// Importamos el componente reutilizable de Botón
import Button from '@/components/Button';
// Importamos los nuevos estilos
import '@/styles/configuracion.css';
import { useConfiguraciones } from '@/hooks/useConfiguraciones';
import permisosService from '@/services/permisosService.js';
import { useAuth } from '@/context/AuthContext';

function Configuracion() {
  const { configuraciones, updateConfiguracion, saveConfiguraciones, loadConfiguraciones } = useConfiguraciones();
  const { rol } = useAuth();
  const [canEditConfig, setCanEditConfig] = useState(false);

  useEffect(() => {
    loadConfiguraciones();
  }, [loadConfiguraciones]);

  useEffect(() => {
    // Administrador siempre puede editar; otros requieren permiso granular
    const check = async () => {
      try {
        const isAdmin = (rol || '').toLowerCase() === 'administrador';
        if (isAdmin) {
          setCanEditConfig(true);
          return;
        }
        const ok = await permisosService.canAccessModule('configuracion', 'editar');
        setCanEditConfig(!!ok);
      } catch {
        setCanEditConfig(false);
      }
    };
    check();
  }, [rol]);

  return (
    <div className="configuracion-container">
      <h1 className="configuracion-title">Panel de Configuración</h1>

      <div className="card-container">
        <div className="card">
          <h3 className="card-title">Gestión de Usuarios</h3>
          <p>Administra usuarios registrados: roles, estado, y eliminación.</p>
          {/* CORRECCIÓN: Usamos Link para la navegación y Button para el estilo */}
          <Link to="/configuracion/usuarios" className="card-link">
            <Button variant="primary">Ir a Usuarios</Button>
          </Link>
        </div>

        <div className="card">
          <h3 className="card-title">Manejo de Mesas</h3>
          <p>Administra las mesas disponibles: estado, reservas, y ubicación.</p>
          <Link to="/configuracion/mesas" className="card-link">
            <Button variant="primary">Ir a Mesas</Button>
          </Link>
        </div>

        <div className="card">
          <h3 className="card-title">Horizonte de Pronóstico</h3>
          <p>Selecciona el horizonte por defecto para pronósticos de ventas.</p>
          <div className="form-row">
            <label htmlFor="horizonte-default" className="me-2">Días</label>
            <input
              id="horizonte-default"
              type="number"
              min={0}
              max={30}
              step={1}
              value={Number(configuraciones?.sistema?.horizonte_pronostico_default ?? 0)}
              onChange={(e) => updateConfiguracion('sistema', 'horizonte_pronostico_default', Math.max(0, Math.min(30, parseInt(e.target.value || '0'))))}
              style={{ width: '6rem' }}
              disabled={!canEditConfig}
            />
            <div className="form-actions" style={{marginTop: 0}}>
              <Button variant="primary" onClick={() => saveConfiguraciones()} disabled={!canEditConfig}>Guardar</Button>
            </div>
          </div>
          <small className="text-muted d-block mt-2">
            {canEditConfig
              ? 'Se guarda localmente y puede sincronizarse con backend.'
              : 'No tienes permisos para editar. Vista solo lectura.'}
          </small>
        </div>

      </div>
      {/* Renderiza rutas hijas directamente cuando se visita /configuracion/usuarios o /configuracion/mesas */}
      <Outlet />
    </div>
  );
};

export default Configuracion;