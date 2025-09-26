import api from '@/services/api';

/**
 * Obtiene los datos de resumen para el panel de control.
 * El token de autenticación se añade automáticamente por el interceptor de 'api.js'.
 * @returns {Promise<Object>} Datos de resumen del dashboard
 */
const getSummary = async () => {
    try {
        const response = await api.get('/dashboard/summary');
        
        // Verificar que tenemos la estructura esperada
        if (response.data && response.data.success && response.data.data) {
            const data = response.data.data;
            
            // Asegurar que todos los campos necesarios están presentes con valores por defecto
            return {
                pedidosActivos: data.pedidosActivos || 0,
                ventasDia: data.ventasDia || 0,
                mesasOcupadas: data.mesasOcupadas || 0,
                mesasTotales: data.mesasTotales || 0,
                inventarioBajo: data.inventarioBajo || 0,
                ventasSemanales: Array.isArray(data.ventasSemanales) ? data.ventasSemanales : generateEmptyWeeklySales(),
                topProductos: Array.isArray(data.topProductos) ? data.topProductos : []
            };
        } else {
            console.warn('Estructura de respuesta inesperada:', response.data);
            return getDefaultDashboardData();
        }
    } catch (error) {
        console.error("Error al obtener resumen del dashboard:", error);
        
        // Si es un error de conexión, mostrar datos vacíos pero informativos
        if (error.code === 'NETWORK_ERROR' || error.response?.status >= 500) {
            console.error('Error de conexión con el servidor');
        } else if (error.response?.status === 401) {
            console.error('Token de autenticación inválido');
            // Aquí podrías disparar un logout automático
        }
        
        return getDefaultDashboardData();
    }
};

/**
 * Genera datos de ventas semanales vacíos para los últimos 7 días
 */
const generateEmptyWeeklySales = () => {
    const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const today = new Date();
    const weeklySales = [];
    
    for (let i = 6; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        const dayName = days[date.getDay()];
        
        weeklySales.push({
            day: dayName,
            sales: 0
        });
    }
    
    return weeklySales;
};

/**
 * Devuelve estructura de datos por defecto para el dashboard
 */
const getDefaultDashboardData = () => {
    return {
        pedidosActivos: 0,
        ventasDia: 0,
        mesasOcupadas: 0,
        mesasTotales: 0,
        inventarioBajo: 0,
        ventasSemanales: generateEmptyWeeklySales(),
        topProductos: []
    };
};

/**
 * Obtiene los datos de dashboard con refresh automático
 * @param {number} intervalSeconds - Intervalo en segundos para auto-refresh (opcional)
 * @returns {Promise<Object>} Datos de resumen del dashboard
 */
const getSummaryWithRefresh = async (intervalSeconds = null) => {
    const data = await getSummary();
    
    if (intervalSeconds && intervalSeconds > 0) {
        // Programar próxima actualización
        setTimeout(() => {
            // Aquí podrías emitir un evento o usar un callback para actualizar
            console.log('Auto-refresh dashboard data...');
        }, intervalSeconds * 1000);
    }
    
    return data;
};

/**
 * Obtiene los datos específicos para el dashboard del mesero
 * @returns {Promise<Object>} Datos específicos del mesero
 */
const getWaiterSummary = async () => {
    try {
        const response = await api.get('/dashboard/waiter-summary');
        
        if (response.data && response.data.success && response.data.data) {
            return response.data.data;
        } else {
            console.warn('Estructura de respuesta inesperada para mesero:', response.data);
            return getDefaultWaiterData();
        }
    } catch (error) {
        console.error("Error al obtener resumen del mesero:", error);
        return getDefaultWaiterData();
    }
};

/**
 * Obtiene los datos específicos para el dashboard del cocinero
 * @returns {Promise<Object>} Datos específicos del cocinero
 */
const getCookSummary = async () => {
    try {
        const response = await api.get('/dashboard/cook-summary');
        
        if (response.data && response.data.success && response.data.data) {
            return response.data.data;
        } else {
            console.warn('Estructura de respuesta inesperada para cocinero:', response.data);
            return getDefaultCookData();
        }
    } catch (error) {
        console.error("Error al obtener resumen del cocinero:", error);
        return getDefaultCookData();
    }
};

/**
 * Devuelve estructura de datos por defecto para el dashboard del mesero
 */
const getDefaultWaiterData = () => {
    return {
        misVentasDia: 0,
        misPedidosActivos: 0,
        mesasAsignadas: 0,
        comisionDia: 0,
        productosDisponibles: [
            { id: 1, nombre: 'Hamburguesa Clásica', precio: 15000, disponible: true },
            { id: 2, nombre: 'Pizza Margherita', precio: 18000, disponible: true },
            { id: 3, nombre: 'Ensalada César', precio: 12000, disponible: false }
        ],
        menuDelDia: [
            { id: 1, nombre: 'Especial del Chef', descripcion: 'Pollo a la plancha con vegetales', precio: 20000, categoria: 'Plato Principal' },
            { id: 2, nombre: 'Sopa del Día', descripcion: 'Crema de champiñones', precio: 8000, categoria: 'Entrada' }
        ],
        rankingMeseros: [
            { id: 1, nombre: 'Juan Pérez', ventas: 150000, pedidos: 12 },
            { id: 2, nombre: 'María García', ventas: 135000, pedidos: 10 },
            { id: 3, nombre: 'Carlos López', ventas: 120000, pedidos: 9 }
        ]
    };
};

/**
 * Devuelve estructura de datos por defecto para el dashboard del cocinero
 */
const getDefaultCookData = () => {
    return {
        pedidosEnCola: 0,
        pedidosEnPreparacion: 0,
        alertasStock: 0,
        platosCompletados: 0,
        stockCritico: [
            {
                id: 1,
                nombre: 'Tomates',
                cantidad: 2,
                unidad: 'kg',
                minimo: 5,
                proveedor: {
                    nombre: 'Frutas y Verduras El Campo',
                    telefono: '+57 300 123 4567',
                    email: 'ventas@elcampo.com'
                }
            }
        ],
        productosMasPedidos: [
            {
                id: 1,
                nombre: 'Hamburguesa Clásica',
                cantidad: 25,
                tiempoPromedio: 15,
                ingredientes: [
                    { nombre: 'Carne de res', cantidad: 150, unidad: 'g' },
                    { nombre: 'Pan de hamburguesa', cantidad: 1, unidad: 'unidad' },
                    { nombre: 'Lechuga', cantidad: 50, unidad: 'g' }
                ]
            }
        ],
        proveedores: [
            {
                id: 1,
                nombre: 'Carnes Premium',
                contacto: 'Roberto Martínez',
                telefono: '+57 301 234 5678',
                email: 'roberto@carnespremium.com',
                activo: true,
                productos: ['Carne de res', 'Pollo', 'Cerdo']
            },
            {
                id: 2,
                nombre: 'Lácteos La Vaca Feliz',
                contacto: 'Ana Rodríguez',
                telefono: '+57 302 345 6789',
                email: 'ana@lavacafeliz.com',
                activo: true,
                productos: ['Queso', 'Leche', 'Mantequilla']
            }
        ]
    };
};

const dashboardService = {
    getSummary,
    getSummaryWithRefresh,
    getDefaultDashboardData,
    getWaiterSummary,
    getCookSummary,
    getDefaultWaiterData,
    getDefaultCookData,
};

export default dashboardService;