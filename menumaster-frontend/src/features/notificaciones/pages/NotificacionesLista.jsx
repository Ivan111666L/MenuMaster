import React, { useEffect, useState } from 'react';
import { getNotificaciones, marcarLeida } from '../services/notificacionService';
import Button from '@/components/Button';

function NotificacionesLista() {
  const [notificaciones, setNotificaciones] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    getNotificaciones()
      .then(setNotificaciones)
      .catch(() => setError('Error al cargar notificaciones'))
      .finally(() => setLoading(false));
  }, []);

  const handleLeida = async (id) => {
    await marcarLeida(id);
    setNotificaciones(notificaciones.map(n => n.id === id ? { ...n, leida: true } : n));
  };

  if (loading) return <div>Cargando notificaciones...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h2>Notificaciones</h2>
      <ul>
        {notificaciones.map(n => (
          <li key={n.id} style={{background:n.leida?'#eee':'#ffe'}}>
            {n.mensaje}
            {!n.leida && <Button onClick={() => handleLeida(n.id)} style={{marginLeft:8}}>Marcar como leída</Button>}
          </li>
        ))}
      </ul>
    </div>
  );
}

export default NotificacionesLista;
