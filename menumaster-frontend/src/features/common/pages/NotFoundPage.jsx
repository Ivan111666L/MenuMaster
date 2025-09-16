import React from 'react';
import { Link } from 'react-router-dom';
import { FaCompass } from 'react-icons/fa'; // Un ícono para la ocasión

// Estilos en línea para un componente simple y autocontenido.
// Puedes moverlos a tu archivo CSS si lo prefieres.
const styles = {
  container: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    alignItems: 'center',
    height: '80vh',
    textAlign: 'center',
    color: '#333',
  },
  icon: {
    fontSize: '5rem',
    marginBottom: '20px',
    color: '#6c757d',
  },
  title: {
    fontSize: '2.5rem',
    fontWeight: 'bold',
    marginBottom: '10px',
  },
  message: {
    fontSize: '1.2rem',
    marginBottom: '30px',
  },
  link: {
    padding: '10px 20px',
    backgroundColor: '#007bff',
    color: 'white',
    textDecoration: 'none',
    borderRadius: '5px',
    fontSize: '1rem',
    transition: 'background-color 0.3s',
  }
};

function NotFoundPage() {
  return (
    <div style={styles.container}>
      <FaCompass style={styles.icon} />
      <h1 style={styles.title}>404 - Página No Encontrada</h1>
      <p style={styles.message}>
        Lo sentimos, la página que buscas no existe o ha sido movida.
      </p>
      <Link to="/dashboard" style={styles.link} 
        onMouseOver={e => e.currentTarget.style.backgroundColor = '#0056b3'}
        onMouseOut={e => e.currentTarget.style.backgroundColor = '#007bff'}
      >
        Volver al Dashboard
      </Link>
    </div>
  );
}

export default NotFoundPage;