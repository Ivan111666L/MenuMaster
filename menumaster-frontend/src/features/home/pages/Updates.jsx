import '@/styles/public.css';

const RELEASES = [
  { version: 'v1.2.0', date: '2024-09-15', notes: ['Mejoras en inventario', 'Nuevo flujo de facturación', 'Optimización del dashboard'] },
  { version: 'v1.1.0', date: '2024-08-10', notes: ['Gestión avanzada de mesas', 'Correcciones de estabilidad'] },
  { version: 'v1.0.0', date: '2024-07-01', notes: ['Lanzamiento inicial', 'Módulo de pedidos', 'Catálogo de productos'] },
];

export default function Updates() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Actualizaciones</h1>
          <p className="public-subtitle">Historial de cambios y mejoras recientes.</p>
        </header>

        <section className="cards-grid">
          {RELEASES.map((rel, idx) => (
            <div key={idx} className="card">
              <h3>{rel.version}</h3>
              <p className="badge info">{rel.date}</p>
              <ul className="list">
                {rel.notes.map((n, i) => <li key={i}>• {n}</li>)}
              </ul>
            </div>
          ))}
        </section>
      </div>
    </div>
  );
}