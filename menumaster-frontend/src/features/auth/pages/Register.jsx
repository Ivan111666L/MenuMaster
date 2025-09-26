import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

// --- Importaciones de Arquitectura y Componentes ---
import authService from '@/features/auth/services/authService';
import Input from '@/components/Input';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import { FaEye, FaEyeSlash } from 'react-icons/fa';
// Importamos el logo
import MenuMasterLogo from '@/assets/logo.png';

// Define el estado inicial para limpiar el formulario fácilmente
const estadoInicialFormulario = {
    nombre: '',
    email: '',
    password: '',
    passwordConfirm: '',
    rol: 'administrador', // El rol por defecto
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

    // Validación mejorada que coincide con los requisitos del backend
    const validateForm = () => {
        const newErrors = {};
        
        if (!formData.nombre.trim()) {
            newErrors.nombre = 'El nombre es obligatorio.';
        } else if (formData.nombre.trim().length < 2) {
            newErrors.nombre = 'El nombre debe tener al menos 2 caracteres.';
        }
        
        if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = 'El formato del email no es válido.';
        }
        
        // Validación de contraseña que coincide con el backend
        if (!isValidPassword(formData.password)) {
            newErrors.password = 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.';
        }
        
        if (formData.password !== formData.passwordConfirm) {
            newErrors.passwordConfirm = 'Las contraseñas no coinciden.';
        }
        
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    // Función de validación de contraseña que coincide exactamente con el backend
    const isValidPassword = (password) => {
        return password.length >= 8 &&
               /[A-Z]/.test(password) &&  // Al menos una mayúscula
               /[a-z]/.test(password) &&  // Al menos una minúscula
               /[0-9]/.test(password);    // Al menos un número
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
            // Mejor manejo de errores del backend
            let errorMessage = 'Error de conexión. Inténtalo de nuevo.';
            
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            } else if (error.response?.data?.error) {
                errorMessage = error.response.data.error;
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            setApiError(errorMessage);
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
                        autoComplete="name"
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
                        autoComplete="email"
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
                            <option value="administrador">Administrador</option>
                            <option value="mesero">Mesero</option>
                            <option value="cocinero">Cocinero</option>
                            <option value="cajero">Cajero</option>
                        </select>
                    </div>

                    <div className="password-input-wrapper">
                        <Input
                            id="password"
                            label="Contraseña"
                            type={showPassword ? 'text' : 'password'}
                            placeholder='Ej: MiClave123'
                            value={formData.password}
                            onChange={handleInputChange}
                            error={errors.password}
                            disabled={isLoading}
                            autoComplete="new-password"
                        />
                        <span onClick={() => setShowPassword(!showPassword)} className="password-toggle-icon">
                            {showPassword ? <FaEyeSlash /> : <FaEye />}
                        </span>
                    </div>

                    {/* Indicadores de requisitos de contraseña */}
                    <div className="password-requirements">
                        <p className="password-requirements-title">La contraseña debe contener:</p>
                        <ul className="password-requirements-list">
                            <li className={formData.password.length >= 8 ? 'requirement-met' : 'requirement-unmet'}>
                                ✓ Al menos 8 caracteres
                            </li>
                            <li className={/[A-Z]/.test(formData.password) ? 'requirement-met' : 'requirement-unmet'}>
                                ✓ Una letra mayúscula (A-Z)
                            </li>
                            <li className={/[a-z]/.test(formData.password) ? 'requirement-met' : 'requirement-unmet'}>
                                ✓ Una letra minúscula (a-z)
                            </li>
                            <li className={/[0-9]/.test(formData.password) ? 'requirement-met' : 'requirement-unmet'}>
                                ✓ Un número (0-9)
                            </li>
                        </ul>
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
                            autoComplete="new-password"
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