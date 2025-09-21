import api from '@/services/api';

const inventarioService = {
    async getInventario() {
        try {
            const response = await api.get('/api/inventario');
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al obtener el inventario' };
        }
    },

    async updateInventario(id, data) {
        try {
            const response = await api.put(`/api/inventario/${id}`, data);
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al actualizar el inventario' };
        }
    },

    async getMovimientos() {
        try {
            const response = await api.get('/api/movimientosinventario');
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al obtener los movimientos' };
        }
    },

    async createMovimiento(data) {
        try {
            const response = await api.post('/api/movimientosinventario', data);
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al crear el movimiento' };
        }
    }
};

export default inventarioService;