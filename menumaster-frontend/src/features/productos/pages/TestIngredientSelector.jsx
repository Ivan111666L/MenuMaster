import React, { useState } from 'react';
import SelectorIngredientes from '@/features/productos/components/SelectorIngredientes';
import '@/styles/productos.css';

const TestIngredientSelector = () => {
    const [ingredientesSeleccionados, setIngredientesSeleccionados] = useState([]);

    const handleIngredientesChange = (ingredientes) => {
        console.log('Ingredientes seleccionados:', ingredientes);
        setIngredientesSeleccionados(ingredientes);
    };

    return (
        <div className="app-container">
            <div className="productos-form-wrapper">
                <h1 className="productos-title">Test Selector de Ingredientes</h1>
                <p className="productos-description">
                    Página de prueba para el componente SelectorIngredientes
                </p>
                
                <SelectorIngredientes
                    ingredientesSeleccionados={ingredientesSeleccionados}
                    onIngredientesChange={handleIngredientesChange}
                />

                <div style={{ marginTop: '2rem', padding: '1rem', border: '1px solid #ccc', borderRadius: '4px' }}>
                    <h3>Ingredientes Seleccionados:</h3>
                    <pre>{JSON.stringify(ingredientesSeleccionados, null, 2)}</pre>
                </div>
            </div>
        </div>
    );
};

export default TestIngredientSelector;