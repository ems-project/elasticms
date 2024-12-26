import { defineConfig } from 'vite'
import inject from '@rollup/plugin-inject'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  server: {
    host: 'localhost',
    port: 5173,
    strictPort: true,
    hmr: true,
  },
  plugins: [
    inject({
      jQuery: 'jquery',
      $: 'jquery'
    })
  ],
  resolve: {
    alias: {
      '~bootstrap': fileURLToPath(new URL('./node_modules/bootstrap', import.meta.url)),
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    manifest: true,
    outDir: '../src/Resources/public',
    sourcemap: true,
    emptyOutDir: true,
    copyPublicDir: true,
    rollupOptions: {
      input: {
        action: 'src/action.js',
        app: 'src/app.js',
        calendar: 'src/calendar.js',
        'criteria-table': 'src/criteria-table.js',
        'criteria-view': 'src/criteria-view.js',
        'edit-revision': 'src/edit-revision.js',
        hierarchical: 'src/hierarchical.js',
        i18n: 'src/i18n.js',
        index: 'index.html',
        'managed-alias': 'src/managed-alias.js'
      }
    }
  }
})
