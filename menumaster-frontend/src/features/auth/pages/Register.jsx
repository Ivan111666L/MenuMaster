import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

// --- Importaciones de Arquitectura y Componentes ---
import authService from '../services/authService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import { FaEye, FaEyeSlash } from 'react-icons/fa';

// Define el estado inicial para limpiar el formulario fácilmente
const estadoInicialFormulario = {
    nombre: '',
    email: '',
    password: '',
    passwordConfirm: '',
    rol: 'mesero', // El rol por defecto
};

function Register() {
    const [formData, setFormData] = useState(estadoInicialFormulario);
    const [errors, setErrors] = useState({});
    const [apiError, setApiError] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const navigate = useNavigate();

    const handleInputChange = (e) => {
        const { id, value } = e.target;
        setFormData(prevState => ({
            ...prevState,
            [id]: value,
        }));
    };

    // La función de validación del lado del cliente está muy bien, la conservamos.
    const validateForm = () => {
        const newErrors = {};
        if (!formData.nombre.trim()) newErrors.nombre = 'El nombre es obligatorio.';
        if (!/\S+@\S+\.\S+/.test(formData.email)) newErrors.email = 'El formato del email no es válido.';
        if (formData.password.length < 8) newErrors.password = 'La contraseña debe tener al menos 8 caracteres.';
        if (formData.password !== formData.passwordConfirm) newErrors.passwordConfirm = 'Las contraseñas no coinciden.';
        
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleRegister = async (e) => {
        e.preventDefault();
        setApiError('');
        if (!validateForm()) return;

        setIsLoading(true);

        try {
            const { passwordConfirm, ...userData } = formData;
            const responseData = await authService.register(userData);

            // Muestra mensaje de éxito y redirige tras un momento
            alert(responseData.mensaje || '¡Registro exitoso!');
            navigate('/login');

        } catch (error) {
            // CORRECCIÓN: Se mejora la captura del mensaje de error específico del backend.
            const errorMessage = error.response?.data?.error || error.message || 'Error de conexión. Inténtalo de nuevo.';
            setApiError(errorMessage);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className='auth-container'>
            <div className="auth-form-container">
                <h1 className='auth-title'>Crear una Cuenta</h1>
                <form onSubmit={handleRegister} noValidate>
                    
                    <Input
                        id="nombre"
                        label="Nombre Completo"
                        type="text"
                        placeholder='Tu nombre'
                        value={formData.nombre}
                        onChange={handleInputChange}
                        error={errors.nombre}
                        disabled={isLoading}
                    />

                    <Input
                        id="email"
                        label="Email"
                        type="email"
                        placeholder='tu@email.com'
                        value={formData.email}
                        onChange={handleInputChange}
                        error={errors.email}
                        disabled={isLoading}
                    />

                    {/* MEJORA: Se añade un selector de rol */}
                    <div className="form-group">
                        <label htmlFor="rol" className="form-label">Quiero registrarme como:</label>
                        <select
                            id="rol"
                            className="form-input"
                            value={formData.rol}
                            onChange={handleInputChange}
                            disabled={isLoading}
                        >
                            <option value="mesero">Mesero</option>
                            <option value="cocinero">Cocinero</option>
                            {/* Un admin probablemente se crea desde otro panel, no por registro público */}
                        </select>
                    </div>

                    <div className="password-input-wrapper">
                        <Input
                            id="password"
                            label="Contraseña"
                            type={showPassword ? 'text' : 'password'}
                            placeholder='Mínimo 8 caracteres'
                            value={formData.password}
                            onChange={handleInputChange}
                            error={errors.password}
                            disabled={isLoading}
                        />
                        <span onClick={() => setShowPassword(!showPassword)} className="password-toggle-icon">
                            {showPassword ? <FaEyeSlash /> : <FaEye />}
                        </span>
                    </div>

                    <div className="password-input-wrapper">
                        <Input
                            id="passwordConfirm"
                            label="Confirmar Contraseña"
                            type={showPassword ? 'text' : 'password'}
                            placeholder='Repite la contraseña'
                            value={formData.passwordConfirm}
                            onChange={handleInputChange}
                            error={errors.passwordConfirm}
                            disabled={isLoading}
                        />
                    </div>
                    
                    {apiError && <div className="auth-error-message">{apiError}</div>}

                    <Button type='submit' variant="primary" className="w-full" disabled={isLoading}>
                        {isLoading ? <Spinner /> : 'Crear Cuenta'}
                    </Button>
                </form>

                <p className="auth-switch">
                    ¿Ya tienes una cuenta? <Link to="/login" className="auth-link">Inicia Sesión</Link>
                </p>
            </div>
        </div>
    );
}

export default Register;