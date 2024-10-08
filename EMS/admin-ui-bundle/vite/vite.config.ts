import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    lib: {
      entry: [
        resolve(__dirname, 'src/test.ts'),
        resolve(__dirname, 'src/app.js'),
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