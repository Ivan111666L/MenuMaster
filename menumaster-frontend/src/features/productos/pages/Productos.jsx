
import React from 'react';
import { Link } from 'react-router-dom';

const Productos = () => {
  return (
    <div style={styles.container}>
      <h1 style={styles.title}>Productos</h1>

      <div style={styles.cardContainer}>
        <div style={styles.card}>
          <h3 style={styles.cardTitle}>Productos Nuevos</h3>
          <p>Crea Nuevos Productos</p>
          <Link to="/Productos/nuevos" style={styles.button}>
            Nuevo
          </Link>
        </div>

        <div style={styles.card}>
          <h3 style={styles.cardTitle}>Productos Creados</h3>
          <p>Administra las Entradas,Platos Fuertes,Bebidas,Postres</p>
          <Link to="/Productos/creados" style={styles.button}>
             Productos Creados
          </Link>
        </div>

        {/* Puedes agregar más tarjetas aquí en el futuro */}
        
      </div>
    </div>
  );
};

const styles = {
  container: {
    padding: '2rem',
    fontFamily: 'Segoe UI, sans-serif',
  },
  title: {
    fontSize: '2rem',
    marginBottom: '2rem',
    color: '#333',
  },
  cardContainer: {
    display: 'flex',
    gap: '2rem',
    flexWrap: 'wrap',
  },
  card: {
    border: '1px solid #ddd',
    borderRadius: '10px',
    padding: '1.5rem',
    width: '300px',
    boxShadow: '0 2px 8px rgba(0,0,0,0.05)',
    backgroundColor: '#fafafa',
  },
  cardTitle: {
    marginBottom: '0.5rem',
    color: '#007bff',
  },
  button: {
    display: 'inline-block',
    marginTop: '1rem',
    padding: '0.6rem 1.2rem',
    backgroundColor: '#007bff',
    color: '#fff',
    textDecoration: 'none',
    borderRadius: '4px',
    fontWeight: '500',
  },
};

export default Productos;