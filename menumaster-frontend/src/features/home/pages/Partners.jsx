import '@/styles/public.css';

export default function Partners() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Socios</h1>
          <p className="public-subtitle">Programa de partners y colaboraciones.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Integradores</h3>
            <p>Conecta MenuMaster con tus sistemas contables y de pago.</p>
            <a className="cta" href="/contact">Aplicar</a>
          </div>
          <div className="card">
            <h3>Distribuidores</h3>
            <p>Comercializa la plataforma en tu región.</p>
            <a className="cta" href="/contact">Unirse</a>
          </div>
        </section>
      </div>
    </div>
  );
}