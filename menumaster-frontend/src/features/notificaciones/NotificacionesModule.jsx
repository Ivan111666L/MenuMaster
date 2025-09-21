import React, { useState } from 'react';
import NotificacionesLista from './pages/NotificacionesLista';
import LogsLista from './pages/LogsLista';

function NotificacionesModule() {
  const [view, setView] = useState('notificaciones');

  return (
    <div>
      <h1>Notificaciones y Logs</h1>
      <div style={{marginBottom:16}}>
        <button onClick={() => setView('notificaciones')}>Ver Notificaciones</button>
        <button onClick={() => setView('logs')} style={{marginLeft:8}}>Ver Logs</button>
      </div>
      {view === 'notificaciones' && <NotificacionesLista />}
      {view === 'logs' && <LogsLista />}
    </div>
  );
}

export default NotificacionesModule;
