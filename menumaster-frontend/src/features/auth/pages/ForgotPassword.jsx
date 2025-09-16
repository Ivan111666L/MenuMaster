import React, { useState } from 'react';
import { Link } from 'react-router-dom';

// --- Importaciones de Componentes y Servicios ---
import { useAuth } from '@/context/AuthContext'; // Podría ser útil si el usuario ya está logueado
import authService from '@/features/auth/services/authService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';


/**
 * Página para que los usuarios soliciten un enlace de restablecimiento de contraseña.
 */
function ForgotPassword() {
  const [email, setEmail] = useState('');
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false); // Para mostrar el mensaje de éxito

  const handleSubmit = async (e) => {
    e.preventDefault();
    // Limpiamos los estados previos
    setError(null);
    setIsLoading(true);

    if (!email) {
      setError('Por favor, ingresa una dirección de correo.');
      setIsLoading(false);
      return;
    }

    try {
      // Llamamos al servicio que se comunica con la API
      await authService.forgotPassword({ email });
      setIsSubmitted(true); // Si la llamada es exitosa, cambiamos la vista
    } catch (err)  {
      // Si la API devuelve un error, lo mostramos
      setError(err.response?.data?.error || 'Ocurrió un error. Inténtalo de nuevo.');
    } finally {
      setIsLoading(false); // Detenemos la carga en cualquier caso
    }
  };

  // Si el formulario ya fue enviado exitosamente, mostramos un mensaje
  if (isSubmitted) {
    return (
      <div className="auth-container">
        <div className="auth-form-container text-center">
          <h1 className="auth-title">Revisa tu Correo</h1>
          <p className="auth-subtitle">
            Si una cuenta con el correo <strong>{email}</strong> existe, hemos enviado las instrucciones para restablecer la contraseña.
          </p>
          <div className="auth-link-container">
            <Link to="/login" className="auth-link">
              Volver a Iniciar Sesión
            </Link>
          </div>
        </div>
      </div>
    );
  }
  
  

  // Vista del formulario
  return (
    <div className="auth-container">
      <div className="auth-form-container">
        <h1 className="auth-title">Recuperar Contraseña</h1>
        <p className="auth-subtitle">
          Ingresa tu correo y te enviaremos las instrucciones.
        </p>
        <form onSubmit={handleSubmit} className="auth-form">
          <Input
            id="email"
            label="Correo Electrónico"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="Tu correo electrónico"
            disabled={isLoading}
            required
          />
          
          {error && <p className="auth-error-message">{error}</p>}
          
          <Button type="submit" variant="primary" disabled={isLoading} className="w-full">
            {isLoading ? <Spinner /> : 'Enviar Enlace'}
          </Button>
        </form>
        <div className="auth-link-container">
          <Link to="/login" className="auth-link">
            Volver a Iniciar Sesión
          </Link>
        </div>
      </div>
    </div>
  );
}

export default ForgotPassword;