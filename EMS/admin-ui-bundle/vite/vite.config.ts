import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    lib: {
      entry: [
        resolve(__dirname, 'src/app.ts'),
        resolve(__dirname, 'src/i18n.ts'),
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