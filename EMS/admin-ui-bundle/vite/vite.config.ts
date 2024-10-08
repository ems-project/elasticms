import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  resolve: {
    alias: {
      '~bootstrap': resolve(__dirname, 'node_modules/bootstrap'),
    }
  },
  build: {
    lib: {
      entry: [
        resolve(__dirname, 'src/test.ts'),
        resolve(__dirname, 'src/app.js'),
        resolve(__dirname, 'src/action.js'),
        resolve(__dirname, 'src/calendar.js'),
        resolve(__dirname, 'src/criteria-table.js'),
        resolve(__dirname, 'src/criteria-view.js'),
        resolve(__dirname, 'src/edit-revision.js'),
        resolve(__dirname, 'src/hierarchical.js'),
        resolve(__dirname, 'src/i18n.js'),
        resolve(__dirname, 'src/managed-alias.js'),
      ],
      name: 'admin-ui',
    },
    manifest: true,
    assetsInlineLimit: 0,
    outDir: '../src/Resources/public',
    emptyOutDir: true,
    copyPublicDir: true
  }
})