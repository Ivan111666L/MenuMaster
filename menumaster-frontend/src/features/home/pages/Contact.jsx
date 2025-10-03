import { useState } from 'react';
import '@/styles/public.css';

export default function Contact() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [status, setStatus] = useState('idle'); // idle | sending | success | error

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!name || !email || !message) {
      setStatus('error');
      return;
    }
    setStatus('sending');
    // Simulación de envío
    setTimeout(() => {
      setStatus('success');
      setName('');
      setEmail('');
      setMessage('');
    }, 800);
  };

  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Contacto</h1>
          <p className="public-subtitle">Ponte en contacto con nuestro equipo.</p>
        </header>

        <section className="section">
          <form className="form" onSubmit={handleSubmit}>
            <input
              type="text"
              placeholder="Tu nombre"
              value={name}
              onChange={(e) => setName(e.target.value)}
            />
            <input
              type="email"
              placeholder="Tu correo"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <textarea
              rows={5}
              placeholder="Tu mensaje"
              value={message}
              onChange={(e) => setMessage(e.target.value)}
            />
            <button className="cta" type="submit" disabled={status === 'sending'}>
              {status === 'sending' ? 'Enviando...' : 'Enviar mensaje'}
            </button>
          </form>
          {status === 'error' && <p className="badge danger">Completa todos los campos.</p>}
          {status === 'success' && <p className="badge success">¡Mensaje enviado! Te responderemos pronto.</p>}
        </section>
      </div>
    </div>
  );
}