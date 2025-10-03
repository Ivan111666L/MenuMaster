import '@/styles/public.css';

export default function Security() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Seguridad</h1>
          <p className="public-subtitle">Prácticas y compromisos de seguridad de la plataforma.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Encriptación</h3>
            <p>Datos cifrados en tránsito (TLS 1.2+) y en reposo.</p>
          </div>
          <div className="card">
            <h3>Accesos</h3>
            <p>Autenticación robusta y gestión de roles y permisos.</p>
          </div>
          <div className="card">
            <h3>Backups</h3>
            <p>Copias de seguridad automáticas y verificación periódica.</p>
          </div>
          <div className="card">
            <h3>Cumplimiento</h3>
            <p>Buenas prácticas y cumplimiento de normativa vigente.</p>
          </div>
        </section>
      </div>
    </div>
  );
}