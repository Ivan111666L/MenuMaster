import api from '@/services/api';

const productosService = {
    async getCategorias() {
        try {
            const response = await api.get('/api/categorias');
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al obtener las categorías' };
        }
    },

    async createProducto(formData) {
        try {
            const response = await api.post('/api/productos', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al crear el producto' };
        }
    },

    async getProductos(filtros = {}) {
        try {
            const response = await api.get('/api/productos', { params: filtros });
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al obtener los productos' };
        }
    },

    async getProducto(id) {
        try {
            const response = await api.get(`/api/productos/${id}`);
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al obtener el producto' };
        }
    },

    async updateProducto(id, formData) {
        try {
            const response = await api.post(`/api/productos/${id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al actualizar el producto' };
        }
    },

    async deleteProducto(id) {
        try {
            const response = await api.delete(`/api/productos/${id}`);
            return response.data;
        } catch (error) {
            throw error.response?.data || { error: 'Error al eliminar el producto' };
        }
    }
};

export default productosService;