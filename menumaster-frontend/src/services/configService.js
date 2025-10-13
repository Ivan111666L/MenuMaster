import api from '@/services/api';

const configService = {
  async getConfiguraciones() {
    try {
      const res = await api.get('/configuracion');
      // Estructuras posibles: { success, data } o directa
      return res?.data?.data ?? res?.data ?? {};
    } catch (error) {
      console.error('Error al obtener configuraciones del servidor:', error);
      throw error;
    }
  },

  async saveConfiguraciones(config) {
    try {
      const res = await api.post('/configuracion', config);
      return res?.data?.data ?? res?.data ?? config;
    } catch (error) {
      console.error('Error al guardar configuraciones en el servidor:', error);
      throw error;
    }
  }
};

export default configService;