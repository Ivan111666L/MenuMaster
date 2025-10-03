import '@/styles/public.css';

export default function Demo() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Demo</h1>
          <p className="public-subtitle">Explora las principales pantallas y funcionalidades.</p>
        </header>

        <section className="card">
          <h3>¿Cómo probar MenuMaster?</h3>
          <ol className="list">
            <li>Regístrate gratis desde <a href="/register">/register</a>.</li>
            <li>Inicia sesión en <a href="/login">/login</a>.</li>
            <li>Accede a Dashboard, Mesas, Pedidos e Inventario.</li>
          </ol>
          <a className="cta" href="/register">Probar ahora</a>
        </section>

        <section className="section cards-grid">
          <div className="card">
            <h3>Mesas</h3>
            <p>Administra disponibilidad y asignación de mesas.</p>
            <span className="badge info">Vista interactiva</span>
          </div>
          <div className="card">
            <h3>Pedidos</h3>
            <p>Simula toma de pedidos y seguimiento del estado.</p>
            <span className="badge success">Flujo completo</span>
          </div>
          <div className="card">
            <h3>Inventario</h3>
            <p>Control de stock, mermas y alertas.</p>
            <span className="badge warning">Alertas</span>
          </div>
        </section>
      </div>
    </div>
  );
}