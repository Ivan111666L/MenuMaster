import React, { useEffect, useState } from 'react';
import { getLogs } from '../services/notificacionService';

function LogsLista() {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    getLogs()
      .then(setLogs)
      .catch(() => setError('Error al cargar logs'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Cargando logs...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Logs de Actividad</h2>
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Detalle</th>
          </tr>
        </thead>
        <tbody>
          {logs.map(log => (
            <tr key={log.id}>
              <td>{log.fecha}</td>
              <td>{log.usuario_nombre}</td>
              <td>{log.accion}</td>
              <td>{log.detalle}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default LogsLista;
