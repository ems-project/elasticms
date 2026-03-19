import { defineConfig } from 'vite'
import inject from '@rollup/plugin-inject'
import liveReload from 'vite-plugin-live-reload'
import { resolve } from 'path';

export default defineConfig({
  base: './',
  build: {
    manifest: true,
    outDir: '../public',
    sourcemap: false,
    emptyOutDir: true,
    copyPublicDir: true,
    cssMinify: 'esbuild',
    rolldownOptions: {
      input: {
        app: 'src/app.js',
        swaggerui: 'src/swagger-ui.js',
        calendar: 'src/calendar.js',
        'criteria-table': 'src/criteria-table.js',
        'criteria-view': 'src/criteria-view.js',
        'edit-revision': 'src/edit-revision.js',
        hierarchical: 'src/hierarchical.js',
        'managed-alias': 'src/managed-alias.js',
        inline_editor: 'src/core/inline-editor/editor.ts',
        inline_editor_iframe: 'src/core/inline-editor/iframe.ts'
      }
    }
  },
  css: {
    devSourcemap: true
  },
  plugins: [
    liveReload('../templates/**/*.twig'),
    liveReload('../../core-bundle/templates/**/*.twig'),
    inject({
      jQuery: 'jquery',
      $: 'jquery',
      exclude: ['**/*.scss', '**/*.css']
    })
  ],
  resolve: {
    extensions: ['.js', '.ts'],
    alias: {
      '@fonts': resolve('./public/fonts')
    }
  },
  server: {
    host: '0.0.0.0',
    origin: 'http://localhost:5173',
    port: 5173,
    strictPort: true,
    hmr: true,
    watch: {
      usePolling: true
    }
  }
})
