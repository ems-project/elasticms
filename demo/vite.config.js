import { defineConfig } from 'vite';
import inject from '@rollup/plugin-inject'
import liveReload from 'vite-plugin-live-reload'

export default defineConfig({
    base: './',
    build: {
        manifest: true,
        outDir: 'dist',
        sourcemap: false,
        emptyOutDir: true,
        copyPublicDir: true,
        rollupOptions: {
            input: {
                index: 'src/index.js',
                admin: 'src/admin.js',
                reveal: 'src/slideshow.js'
            },
            output: {
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }
                    return 'assets/[name][extname]';
                }
            }
        }
    },
    plugins: [
        liveReload('skeleton/**/*.twig'),
        liveReload('skeleton/**/*.yaml'),
        inject({
            jQuery: 'jquery',
            $: 'jquery',
            exclude: ['**/*.scss', '**/*.css']
        })
    ],
    resolve: {
        extensions: ['.js'],
        alias: {
            '@public': ''
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler'
            }
        }
    },
    server: {
        host: '0.0.0.0',
        origin: 'http://localhost:5174',
        port: 5174,
        strictPort: true,
        hmr: true,
        watch: {
            usePolling: true,
        }
    }
});
