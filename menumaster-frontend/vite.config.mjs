import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path' // <-- 1. Importa 'path' de Node.js

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },

  server: {
    proxy: {
      // Proxy para rutas de API que deben devolver JSON desde el backend PHP
      '/inventario': {
        target: 'http://localhost:80', // Cambia el puerto si tu backend usa otro
        changeOrigin: true,
        secure: false,
        // Opcional: reescribe la ruta si tu backend espera /api/inventario
        // rewrite: (path) => path.replace(/^\/inventario/, '/api/inventario'),
      },
      '/api': {
        target: 'http://localhost:80',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})