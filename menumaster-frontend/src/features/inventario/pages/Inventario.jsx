import React, { useState, useEffect } from 'react';
import IngredienteCreado from '@/features/inventario/pages/IngredienteCreado';
import IngredienteNuevo from '@/features/inventario/pages/IngredienteNuevo';
import '@/styles/global.css'; // Asegúrate de que los estilos globales estén importados



// ===================================================================================
// COMPONENTE PRINCIPAL QUE MANEJA LA NAVEGACIÓN
// ===================================================================================
const Inventario = () => {
    // 'menu' es la vista inicial, 'ver' para la lista, 'nuevo' para el formulario
    const [vistaActual, setVistaActual] = useState('menu');

    const renderizarVista = () => {
        switch (vistaActual) {
            case 'nuevo':
                return <IngredienteNuevo />;
            case 'ver':
                return <IngredienteCreado />;
            case 'menu':
            default:
                return (
                    <div className="menu-container">
                        <h1 className="menu-title">Gestión de Inventario</h1>
                        <p className="menu-subtitle">
                            Selecciona una opción para administrar los ingredientes de tu negocio.
                        </p>
                        <div className="menu-card-container">
                            <div className="menu-card">
                                <h3 className="menu-card-title">Añadir Ingrediente</h3>
                                <p className="menu-card-description">Registra un nuevo ingrediente en el sistema de inventario.</p>
                                <button className="menu-card-button" onClick={() => setVistaActual('nuevo')}>
                                    Nuevo Ingrediente
                                </button>
                            </div>

                            <div className="menu-card">
                                <h3 className="menu-card-title">Ver Inventario</h3>
                                <p className="menu-card-description">Consulta, busca y revisa el stock de todos tus ingredientes.</p>
                                <button className="menu-card-button" onClick={() => setVistaActual('ver')}>
                                    Ver Ingredientes
                                </button>
                            </div>
                        </div>
                    </div>
                );
        }
    };

    return (
        <>
            <div className="inventario-app">
                <div className="inventario-container">
                    {vistaActual !== 'menu' && (
                        <button className="btn-volver" onClick={() => setVistaActual('menu')}>
                            &larr; Volver al Menú
                        </button>
                    )}
                    {renderizarVista()}
                </div>
            </div>
        </>
    );
};

export default Inventario;