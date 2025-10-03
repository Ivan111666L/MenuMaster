import React, { useState } from 'react';
import NotificacionesLista from './pages/NotificacionesLista';
import LogsLista from './pages/LogsLista';
import '@/styles/notificaciones.css';

function NotificacionesModule() {
  const [view, setView] = useState('notificaciones');

  return (
    <div className="notificaciones-app main-content">
      <div className="section-header">
        <h1>Notificaciones y Logs</h1>
        <p className="section-subtitle">Consulta eventos del sistema y alertas.</p>
      </div>
      <div className="button-group">
        <button className={`btn-tab ${view==='notificaciones'?'active':''}`} onClick={() => setView('notificaciones')}>Ver Notificaciones</button>
        <button className={`btn-tab ${view==='logs'?'active':''}`} onClick={() => setView('logs')}>Ver Logs</button>
      </div>
      <div className="panel">
        {view === 'notificaciones' && <NotificacionesLista />}
        {view === 'logs' && <LogsLista />}
      </div>
    </div>
  );
}

export default NotificacionesModule;
