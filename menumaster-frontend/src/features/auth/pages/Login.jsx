import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

// --- Importaciones de Arquitectura ---
import { useAuth } from '@/context/AuthContext';
import authService from '@/features/auth/services/authService'; // Tu servicio de API

// --- Importaciones de Componentes Reutilizables ---
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import { FaEye, FaEyeSlash } from 'react-icons/fa'; // Iconos para el toggle de contraseña

// --- Estilos ---
import '@/styles/auth.css';

function Login() {
  const [formData, setFormData] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const { login } = useAuth();
  const navigate = useNavigate();

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
      // CORRECCIÓN: Se llama a la función `login` del servicio de API.
      // Ya no se llama a `api.post` directamente desde aquí.
      const response = await authService.login(formData);

      if (response.token && response.usuario) {
        // CORRECCIÓN: Se llama a la función `login` del AuthContext para guardar la sesión.
        login(response);
        
        // Tu excelente lógica de redirección por rol.
        const roleRedirects = {
          administrador: '/dashboard',
          cajero: '/facturacion',
          cocinero: '/cocina',
          mesero: '/mesas',
        };
        const path = roleRedirects[response.usuario.rol] || '/dashboard'; // Ruta por defecto
        navigate(path);

      } else {
        throw new Error(response.error || 'Credenciales incorrectas.');
      }

    } catch (err) {
      const errorMessage = err.response?.data?.error || err.message || 'Error de conexión. Inténtalo de nuevo.';
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className='auth-container'>
      <div className="auth-form-container">
        <h1 className='auth-title'>Iniciar Sesión</h1>
        <p className="auth-subtitle">Bienvenido de nuevo a MenuMaster</p>

        <form onSubmit={handleSubmit} noValidate>
          {/* CORRECCIÓN: Se usa el componente reutilizable <Input> */}
          <Input
            id="email"
            label="Email"
            type="email"
            placeholder='tu@email.com'
            required
            value={formData.email}
            onChange={handleInputChange}
            disabled={isLoading}
          />

          {/* CORRECCIÓN: Se usa <Input> para la contraseña con el toggle */}
          <div className="password-input-wrapper">
            <Input
              id="password"
              label="Contraseña"
              type={showPassword ? 'text' : 'password'}
              placeholder='Tu contraseña'
              required
              value={formData.password}
              onChange={handleInputChange}
              disabled={isLoading}
            />
            <span onClick={() => setShowPassword(!showPassword)} className="password-toggle-icon">
              {showPassword ? <FaEyeSlash /> : <FaEye />}
            </span>
          </div>

          {error && <div className="auth-error-message">{error}</div>}

          {/* CORRECCIÓN: Se usa el componente reutilizable <Button> */}
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