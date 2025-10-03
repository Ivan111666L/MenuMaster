import '@/styles/public.css';

const SERVICES = [
  { name: 'API Principal', status: 'operational' },
  { name: 'Base de Datos', status: 'operational' },
  { name: 'Procesamiento de Pagos', status: 'operational' },
  { name: 'Notificaciones', status: 'degraded' },
];

const statusBadge = (s) => {
  if (s === 'operational') return <span className="badge success">Operativo</span>;
  if (s === 'degraded') return <span className="badge warning">Degradado</span>;
  return <span className="badge danger">Incidente</span>;
};

export default function Status() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Estado del Sistema</h1>
          <p className="public-subtitle">Actualización de disponibilidad de servicios.</p>
        </header>

        <section className="cards-grid">
          {SERVICES.map((svc, idx) => (
            <div key={idx} className="card">
              <h3>{svc.name}</h3>
              <p>{statusBadge(svc.status)}</p>
              <p>Última verificación: hace 2 min</p>
            </div>
          ))}
        </section>
      </div>
    </div>
  );
}