import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue({
      script: {
        defineModel: true
      },
  }),
    // vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@layout': fileURLToPath(new URL('./src/layout', import.meta.url)),
      '@components': fileURLToPath(new URL('./src/components', import.meta.url))
    },
  },
  base: './', // Use relative paths for assets
  build: {
    manifest: true,
    outDir: "./dist",
    rollupOptions: {
      input: {
        main: "./src/main.ts",
        chatbot: "./src/chatBoat/main.ts"
      },
    },
  },
  server: {
    port: 3000, // Replace 3000 with your desired port
  },
})
