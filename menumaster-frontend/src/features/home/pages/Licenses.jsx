import '@/styles/public.css';

const LIBS = [
  { name: 'React', license: 'MIT', link: 'https://github.com/facebook/react/blob/main/LICENSE' },
  { name: 'Vite', license: 'MIT', link: 'https://github.com/vitejs/vite/blob/main/LICENSE' },
  { name: 'react-router', license: 'MIT', link: 'https://github.com/remix-run/react-router/blob/main/LICENSE.md' },
  { name: 'react-icons', license: 'MIT', link: 'https://github.com/react-icons/react-icons/blob/master/LICENSE' },
];

export default function Licenses() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Licencias</h1>
          <p className="public-subtitle">Atribuciones y licencias de terceros utilizados.</p>
        </header>

        <section className="cards-grid">
          {LIBS.map((lib, idx) => (
            <div key={idx} className="card">
              <h3>{lib.name}</h3>
              <p className="badge info">{lib.license}</p>
              <a className="cta" href={lib.link} target="_blank" rel="noreferrer">Ver licencia</a>
            </div>
          ))}
        </section>
      </div>
    </div>
  );
}