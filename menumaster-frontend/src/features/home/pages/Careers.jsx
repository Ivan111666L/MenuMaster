import '@/styles/public.css';

const JOBS = [
  { title: 'Frontend Developer', type: 'Remoto', link: '/contact' },
  { title: 'UX/UI Designer', type: 'Híbrido', link: '/contact' },
  { title: 'Account Manager', type: 'Presencial', link: '/contact' },
];

export default function Careers() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Carreras</h1>
          <p className="public-subtitle">Únete al equipo y crece con nosotros.</p>
        </header>

        <section className="cards-grid">
          {JOBS.map((job, idx) => (
            <div key={idx} className="card">
              <h3>{job.title}</h3>
              <span className="badge info">{job.type}</span>
              <p>Envíanos tu CV y portfolio.</p>
              <a className="cta" href={job.link}>Postular</a>
            </div>
          ))}
        </section>
      </div>
    </div>
  );
}