import { defineConfig } from 'vite'

export default defineConfig({
  base: './',
  build: {
    manifest: true,
    outDir: '../public',
    sourcemap: false,
    emptyOutDir: true,
    copyPublicDir: true,
    rollupOptions: {
      input: {
        live_editor: 'src/live_editor.js'
      }
    }
  },
  css: {
    devSourcemap: true
  },
  resolve: {
    extensions: ['.js', '.ts'],
  },
  server: {
    host: '0.0.0.0',
    origin: 'http://localhost:5173',
    port: 5173,
    strictPort: true,
    hmr: true,
    watch: {
      usePolling: true,
    }
  }
})
