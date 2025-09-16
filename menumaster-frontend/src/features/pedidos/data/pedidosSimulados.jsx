// src/data/pedidosSimulados.js

export const pedidosSimulados = [
    {
        id: 1,
        mesa: 5,
        estado: 'pendiente',
        fecha: '2023-10-27T10:00:00Z',
        items: [
            { id: 1, nombre: 'Hamburguesa Clásica', cantidad: 2, precio: 8.50 },
            { id: 2, nombre: 'Papas Fritas Grandes', cantidad: 1, precio: 3.00 },
            { id: 3, nombre: 'Refresco de Cola', cantidad: 2, precio: 2.50 }
        ],
        total: 25.00
    },
    {
        id: 2,
        mesa: 3,
        estado: 'pagado',
        fecha: '2023-10-27T09:45:00Z',
        items: [
            { id: 4, nombre: 'Ensalada César', cantidad: 1, precio: 7.00 },
            { id: 5, nombre: 'Pizza Margarita', cantidad: 1, precio: 12.00 }
        ],
        total: 19.00
    },
    {
        id: 3,
        mesa: 8,
        estado: 'pendiente',
        fecha: '2023-10-27T10:15:00Z',
        items: [
            { id: 1, nombre: 'Hamburguesa Clásica', cantidad: 1, precio: 8.50 },
            { id: 3, nombre: 'Refresco de Cola', cantidad: 1, precio: 2.50 }
        ],
        total: 11.00
    },
];
export default pedidosSimulados;