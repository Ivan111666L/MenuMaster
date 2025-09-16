import React, { useState, useEffect } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';

import authService from '@/features/auth/services/authService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';

function ResetPassword() {
  // 1. Obtenemos los parámetros de la URL (para leer el token)
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  // 2. Estados del componente
  const [token, setToken] = useState(null);
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  // 3. Al cargar la página, leemos el token de la URL
  useEffect(() => {
    const urlToken = searchParams.get('token');
    if (urlToken) {
      setToken(urlToken);
    } else {
      setError('Token no encontrado o inválido.');
    }
  }, [searchParams]);

  // 4. Lógica para manejar el envío del formulario
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);

    // Validaciones
    if (password.length < 8) {
      setError('La contraseña debe tener al menos 8 caracteres.');
      return;
    }
    if (password !== confirmPassword) {
      setError('Las contraseñas no coinciden.');
      return;
    }

    setIsLoading(true);
    try {
      await authService.resetPassword({ token, password, confirmPassword });
      setIsSuccess(true);
    } catch (err) {
      setError(err.response?.data?.error || 'No se pudo restablecer la contraseña. El token puede ser inválido o haber expirado.');
    } finally {
      setIsLoading(false);
    }
  };

  // Vista de éxito
  if (isSuccess) {
    return (
      <div className="auth-container">
        <div className="auth-form-container text-center">
          <h1 className="auth-title">Contraseña Actualizada</h1>
          <p className="auth-subtitle">
            Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión.
          </p>
          <Link to="/login" className="auth-link">
            <Button variant="primary">Ir a Iniciar Sesión</Button>
          </Link>
        </div>
      </div>
    );
  }

  // Vista del formulario
  return (
    <div className="auth-container">
      <div className="auth-form-container">
        <h1 className="auth-title">Restablecer Contraseña</h1>
        <p className="auth-subtitle">
          Ingresa tu nueva contraseña.
        </p>
        <form onSubmit={handleSubmit} className="auth-form">
          <Input
            id="password"
            label="Nueva Contraseña"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            disabled={isLoading || !token}
            required
          />
          <Input
            id="confirmPassword"
            label="Confirmar Nueva Contraseña"
            type="password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            disabled={isLoading || !token}
            required
          />
          
          {error && <p className="auth-error-message">{error}</p>}
          
          <Button type="submit" variant="primary" disabled={isLoading || !token} className="w-full">
            {isLoading ? <Spinner /> : 'Restablecer Contraseña'}
          </Button>
        </form>
      </div>
    </div>
  );
}

export default ResetPassword;