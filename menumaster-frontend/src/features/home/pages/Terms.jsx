import '@/styles/public.css';

export default function Terms() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Términos</h1>
          <p className="public-subtitle">Resumen de términos y condiciones de uso.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Uso Aceptable</h3>
            <p>No realizar actividades fraudulentas ni vulnerar la seguridad.</p>
          </div>
          <div className="card">
            <h3>Cuenta</h3>
            <p>El usuario es responsable de la confidencialidad de sus credenciales.</p>
          </div>
          <div className="card">
            <h3>Pagos</h3>
            <p>Los planes se facturan según la modalidad seleccionada.</p>
          </div>
          <div className="card">
            <h3>Limitación de Responsabilidad</h3>
            <p>La plataforma se ofrece “tal cual” dentro de estándares de calidad.</p>
          </div>
        </section>
      </div>
    </div>
  );
}