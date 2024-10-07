import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    lib: {
      entry: [
        resolve(__dirname, 'src/app.ts'),
        resolve(__dirname, 'src/i18n.ts'),
      ],
      name: 'core-app',
    },
    manifest: 'manifest.json',
    outDir: '../src/Resources/public',
    emptyOutDir: true,
  }
})