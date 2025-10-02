import React, { useState, useEffect, useCallback } from 'react';

// --- Importaciones de Arquitectura y Componentes ---
import menuDelDiaService from '@/features/cocina/services/menuDelDiaService';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/cocina.css'; // Estilos dedicados para esta página

function MenuDelDia() {
    // --- Estados del Componente ---
    const [allProducts, setAllProducts] = useState([]);
    const [menuDelDia, setMenuDelDia] = useState([]);
    const [selectedProductId, setSelectedProductId] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // --- Carga de Datos ---
    // Usamos useCallback para que la función no se re-cree en cada renderizado
    const fetchData = useCallback(async () => {
        try {
            // Pedimos ambas listas en paralelo para más eficiencia
            const [productosData, menuData] = await Promise.all([
                menuDelDiaService.getAllProducts(),
                menuDelDiaService.getMenuDelDia(),
            ]);
            setAllProducts(productosData);
            setMenuDelDia(menuData);
        } catch (err) {
            setError('No se pudieron cargar los datos. Inténtalo de nuevo.');
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    // --- Acciones ---
    const agregarProducto = async () => {
        if (!selectedProductId) return;
        try {
            await menuDelDiaService.addProductToMenu(selectedProductId);
            await fetchData(); // Recargamos los datos para reflejar el cambio
            setSelectedProductId(''); // Reseteamos el dropdown
        } catch (err) {
            alert('Error al agregar el producto.');
        }
    };

    const eliminarProducto = async (productoId) => {
        if (window.confirm('¿Quitar este producto del menú del día?')) {
            try {
                await menuDelDiaService.removeProductFromMenu(productoId);
                await fetchData(); // Recargamos los datos
            } catch (err) {
                alert('Error al eliminar el producto.');
            }
        }
    };

    // --- Renderizado ---
    if (isLoading) return <Spinner />;
    if (error) return <div className="error-message">{error}</div>;

    // Filtramos la lista de productos para el dropdown
    const idsEnMenu = menuDelDia.map(p => p.producto_id);
    const productosDisponibles = allProducts.filter(p => !idsEnMenu.includes(p.id));

    return (
        <div className="menu-dia-container">
            <h1>Menú del Día</h1>
            <p>Selecciona los platos de tu inventario que estarán disponibles hoy.</p>
            
            <div className="input-group">
                <select
                    className="form-input"
                    value={selectedProductId}
                    onChange={(e) => setSelectedProductId(e.target.value)}
                >
                    <option value="">-- Selecciona un producto --</option>
                    {productosDisponibles.map(producto => (
                        <option key={producto.id} value={producto.id}>
                            {producto.nombre}
                        </option>
                    ))}
                </select>
                <Button onClick={agregarProducto} variant="primary">Agregar</Button>
            </div>

            <ul className="lista-productos-dia">
                {menuDelDia.length === 0 ? (
                    <li>Aún no hay productos en el menú de hoy.</li>
                ) : (
                    menuDelDia.map(item => (
                        <li key={item.id}>
                            <span>{item.nombre_producto} (${parseFloat(item.precio_producto).toFixed(2)})</span>
                            <Button 
                                className="boton-eliminar" 
                                variant="danger" 
                                onClick={() => eliminarProducto(item.producto_id)}
                            >
                                Eliminar
                            </Button>
                        </li>
                    ))
                )}
            </ul>
        </div>
    );
};

export default MenuDelDia;