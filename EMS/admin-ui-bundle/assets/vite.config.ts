import { defineConfig } from 'vite'
import liveReload from 'vite-plugin-live-reload'
import { resolve } from 'path'

const componentChunks = ['MediaLibrary', 'Tiptap', 'UI', 'Wysiwyg']
    .map(name => ({ name: name.toLowerCase(), test: new RegExp(`components/${name}`) }))

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
                inline_editor_iframe: 'src/core/inline-editor/iframe.ts',
                core_bundle: 'src/core-bundle.ts'
            },
            transform: {
                inject: {
                    $: 'jquery',
                    jQuery: 'jquery'
                }
            },
            output: {
                codeSplitting: {
                    groups: [
                        ...componentChunks,
                        { name: 'plugin-media-library', test: /plugins\/mediaLibrary/ },
                        { name: 'plugin-wysiwyg', test: /plugins\/wysiwyg/ }
                    ]
                }
            }
        }
    },
    css: {
        devSourcemap: true
    },
    plugins: [
        liveReload('../templates/**/*.twig'),
        liveReload('../../core-bundle/templates/**/*.twig'),
    ],
    resolve: {
        extensions: ['.js', '.ts'],
        alias: {
            '@fonts': resolve('./public/fonts'),
            '@tabler-icons': resolve('./node_modules/@tabler/icons/icons'),
            '@css': resolve('./css')
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
