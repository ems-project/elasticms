import { defineConfig } from 'vite'
import inject from '@rollup/plugin-inject'

export default defineConfig({
  server: {
    host: '0.0.0.0',
    origin: 'http://localhost:5173',
    port: 5173,
    strictPort: true,
    hmr: true,
    watch: {
      usePolling: true,
    },
  },
  base: './',
  plugins: [
    inject({
      jQuery: 'jquery',
      $: 'jquery',
      exclude: ['**/*.scss', '**/*.css']
    })
  ],
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
        'managed-alias': 'src/managed-alias.js'
      }
    }
  }
})
