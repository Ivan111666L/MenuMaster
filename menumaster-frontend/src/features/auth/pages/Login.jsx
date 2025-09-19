import React, { useState } from 'react';
import { Link } from 'react-router-dom';

// --- Importaciones de Arquitectura y Componentes ---
import { useAuth } from '@/context/AuthContext';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import { FaEye, FaEyeSlash } from 'react-icons/fa';
import '@/styles/auth.css'; // Asegúrate de que los estilos estén aquí
// Importamos el logo
import MenuMasterLogo from '@/assets/menumaster-logo.svg';

function Login() {
  const [formData, setFormData] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  // Obtenemos la función 'login' del contexto.
  // Esta función ya se encarga de todo: llamar a la API, guardar la sesión y redirigir.
  const { login } = useAuth();

  const handleInputChange = (e) => {
    const { id, value } = e.target;
    setFormData(prevState => ({ ...prevState, [id]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.email || !formData.password) {
      setError('Por favor, completa todos los campos.');
      return;
    }
    setIsLoading(true);
    setError('');

    try {
      // La lógica se simplifica a una sola llamada al contexto.
      await login(formData);
      // El AuthContext se encargará de la redirección.

    } catch (err) {
      const errorMessage = err.response?.data?.error || err.message || 'Error de conexión.';
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className='auth-container'>
      <div className="auth-form-container">
        <div className="auth-logo-container">
          <img src={MenuMasterLogo} alt="MenuMaster" className="auth-logo" />
        </div>
        <h1 className='auth-title'>Iniciar Sesión</h1>
        <p className="auth-subtitle">Bienvenido de nuevo a MenuMaster</p>

        <form onSubmit={handleSubmit} noValidate>
          <label htmlFor="email" className="sr-only">Correo electrónico</label>
            <Input 
              id="email"
              className="form-input"
              autoComplete="email"
              required
              type="email"
              name="email"
              placeholder="Ingresa tu correo"
              value={formData.email} 
              onChange={handleInputChange} 
              disabled={isLoading} 
            />
          <label htmlFor="password" className="sr-only">Contraseña</label>
            <div className="password-input-wrapper">
              <Input 
                id="password"
                className="form-input"
                autoComplete="current-password"
                required
                name="password"
                placeholder="Ingresa tu contraseña"
                type={showPassword ? 'text' : 'password'} 
                value={formData.password} 
                onChange={handleInputChange} 
                disabled={isLoading} 
              />
            <span onClick={() => setShowPassword(!showPassword)} className="password-toggle-icon">
              {showPassword ? <FaEyeSlash /> : <FaEye />}
            </span>
          </div>
          {error && <div className="auth-error-message">{error}</div>}
          <Button type='submit' variant="primary" disabled={isLoading} className="w-full">
            {isLoading ? <Spinner /> : 'Ingresar'}
          </Button>
        </form>
        <div className="auth-links-container">
          <Link to="/forgot-password">¿Olvidaste tu contraseña?</Link>
          <Link to="/register">Crear una cuenta</Link>
        </div>
      </div>
    </div>
  );
}

export default Login;