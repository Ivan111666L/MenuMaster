import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path' // <-- 1. Importa 'path' de Node.js

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    {
      name: 'preview-html-charset',
      // Asegura charset en respuestas HTML sólo en servidor de preview
      configurePreviewServer(server) {
        server.middlewares.use((req, res, next) => {
          const accept = req.headers['accept']
          if (accept && accept.includes('text/html')) {
            res.setHeader('Content-Type', 'text/html; charset=utf-8')
          }
          next()
        })
      },
      // Forzar charset en dev server para HTML/JS/CSS por compatibilidad con herramientas
      configureServer(server) {
        server.middlewares.use((req, res, next) => {
          const url = req.url || ''
          const accept = req.headers['accept'] || ''
          // HTML
          if (accept.includes('text/html') || url === '/' || url.startsWith('/dashboard')) {
            res.setHeader('Content-Type', 'text/html; charset=utf-8')
          }
          // JavaScript (módulos y dependencias de Vite)
          if (url.endsWith('.js') || url.includes('/node_modules/.vite/deps/')) {
            res.setHeader('Content-Type', 'text/javascript; charset=utf-8')
          }
          // CSS
          if (url.endsWith('.css')) {
            res.setHeader('Content-Type', 'text/css; charset=utf-8')
          }
          next()
        })
      }
    }
  ],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },

  server: {
    middlewareMode: false,
    open: '/home',
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