import { defineConfig } from 'vite'
import { resolve } from 'path'
import inject from '@rollup/plugin-inject'

export default defineConfig({
  plugins: [
    inject({
      jQuery: 'jquery',
    }),
  ],
  resolve: {
    alias: {
      '~bootstrap': resolve(__dirname, 'node_modules/bootstrap'),
    }
  },
  build: {
    manifest: true,
    outDir: '../src/Resources/public',
    emptyOutDir: true,
    copyPublicDir: true,
    rollupOptions: {
      input: {
        app: 'src/app.js',
        style: 'css/style.scss'
      }
    }
  },
})