import '@/styles/public.css';

export default function Press() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Prensa</h1>
          <p className="public-subtitle">Recursos y comunicados para medios.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Kit de Prensa</h3>
            <p>Logo, paleta de colores, capturas y notas de uso.</p>
            <a className="cta" href="#">Descargar</a>
          </div>
          <div className="card">
            <h3>Contactos</h3>
            <p>Escríbenos para entrevistas y solicitudes.</p>
            <a className="cta" href="/contact">Contactar</a>
          </div>
        </section>
      </div>
    </div>
  );
}