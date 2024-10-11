import { defineConfig } from 'vite'
import { resolve } from 'path'
import inject from '@rollup/plugin-inject'

export default defineConfig({
  server: {
    proxy: {
      '/index.php': {
        target: 'http://127.0.0.1:8881',
        changeOrigin: false,
        secure: false,
        xfwd: true,
      },
      '/bundles': {
        target: 'http://127.0.0.1:8881',
        changeOrigin: false,
        secure: false,
        xfwd: true,
      },
    },
  },
  plugins: [
    inject({
      jQuery: 'jquery',
      $: 'jquery'
    })
  ],
  resolve: {
    alias: {
      '~bootstrap': resolve(__dirname, 'node_modules/bootstrap')
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
        index: 'index.html',
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
