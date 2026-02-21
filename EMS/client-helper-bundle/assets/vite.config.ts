import { defineConfig } from 'vite'
import liveReload from 'vite-plugin-live-reload'

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
        editor: 'src/inline-editor/editor.js',
        iframe: 'src/inline-editor/iframe.js',
        button: 'src/inline-editor/button.js'
      }
    }
  },
  css: {
    devSourcemap: true
  },
  resolve: {
    extensions: ['.js', '.ts'],
  },
  plugins: [
    liveReload('../templates/**/*.twig'),
  ],
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
