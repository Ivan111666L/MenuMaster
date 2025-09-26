// src/services/permisosService.js
import api from '@/services/api.js';

/**
 * Servicio para manejar permisos de usuario
 */
class PermisosService {
  /**
   * Obtener todos los permisos del usuario actual
   * @returns {Promise<Object>} Permisos del usuario
   */
  async getCurrentUserPermissions() {
    try {
      const response = await api.get('/permisos/current-user');
      return response.data;
    } catch (error) {
      console.error('Error al obtener permisos del usuario:', error);
      throw error;
    }
  }

  /**
   * Verificar si el usuario tiene un permiso específico
   * @param {string} permiso - Nombre del permiso a verificar
   * @returns {Promise<boolean>} True si tiene el permiso
   */
  async checkPermission(permiso) {
    try {
      const response = await api.post('/permisos/check', { permiso });
      return response.data?.data?.tiene_permiso || false;
    } catch (error) {
      console.error('Error al verificar permiso:', error);
      return false;
    }
  }

  /**
   * Obtener permisos agrupados por módulo
   * @returns {Promise<Object>} Permisos agrupados por módulo
   */
  async getPermissionsByModule() {
    try {
      const response = await this.getCurrentUserPermissions();
      const permisos = response.data?.permisos || [];
      
      // Agrupar permisos por módulo
      const permisosPorModulo = {};
      permisos.forEach(permiso => {
        if (!permisosPorModulo[permiso.modulo]) {
          permisosPorModulo[permiso.modulo] = [];
        }
        permisosPorModulo[permiso.modulo].push(permiso);
      });
      
      return permisosPorModulo;
    } catch (error) {
      console.error('Error al obtener permisos por módulo:', error);
      return {};
    }
  }

  /**
   * Verificar si el usuario puede acceder a un módulo específico
   * @param {string} modulo - Nombre del módulo
   * @param {string} accion - Acción específica (opcional)
   * @returns {Promise<boolean>} True si puede acceder
   */
  async canAccessModule(modulo, accion = null) {
    try {
      const permisosPorModulo = await this.getPermissionsByModule();
      const permisosModulo = permisosPorModulo[modulo] || [];
      
      if (accion) {
        return permisosModulo.some(p => p.accion === accion);
      }
      
      return permisosModulo.length > 0;
    } catch (error) {
      console.error('Error al verificar acceso al módulo:', error);
      return false;
    }
  }

  /**
   * Obtener lista de módulos accesibles para el usuario
   * @returns {Promise<Array>} Lista de módulos accesibles
   */
  async getAccessibleModules() {
    try {
      const permisosPorModulo = await this.getPermissionsByModule();
      return Object.keys(permisosPorModulo);
    } catch (error) {
      console.error('Error al obtener módulos accesibles:', error);
      return [];
    }
  }
}

// Crear instancia singleton
const permisosService = new PermisosService();

export default permisosService;

// Exportar métodos individuales para facilitar el uso
export const {
  getCurrentUserPermissions,
  checkPermission,
  getPermissionsByModule,
  canAccessModule,
  getAccessibleModules
} = permisosService;