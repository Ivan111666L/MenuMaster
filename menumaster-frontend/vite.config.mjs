import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path' // <-- 1. Importa 'path' de Node.js

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  
  // --- AÑADE O COMPLETA ESTA SECCIÓN ---
  resolve: {
    alias: {
      // Le decimos a Vite que cada vez que vea '@', lo reemplace por la ruta a la carpeta 'src'.
      '@': path.resolve(__dirname, './src'),
    },
  },
  // ------------------------------------
})