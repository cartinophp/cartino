import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";

const isDev = process.env.NODE_ENV === 'development';
const isPackageDev = process.env.CARTINO_DEV === 'true';

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      "@": resolve(__dirname, "resources/js"),
      "~": resolve(__dirname, "resources"),
      // In dev mode, punta al source della UI lib per hot reload
      ...(isPackageDev && {
        "@cartino/ui": resolve(__dirname, "../ui/src/index.ts"),
      }),
    },
  },
  define: {
    __VUE_OPTIONS_API__: false,
    __VUE_PROD_DEVTOOLS__: false,
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
  },
  publicDir: false, // Disable public directory copying to avoid recursive issues
  build: {
    outDir: isPackageDev ? "public/build" : "public/vendor/cartino",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, "resources/js/app.js"),
      },
      output: {
        manualChunks: {
          vendor: ["vue", "pinia", "@inertiajs/vue3"],
          ui: ["@heroicons/vue", "reka-ui"],
        },
        assetFileNames: 'assets/[name]-[hash][extname]',
        chunkFileNames: 'assets/[name]-[hash].js',
        entryFileNames: 'assets/[name]-[hash].js',
      },
    },
    chunkSizeWarningLimit: 1000,
  },
  server: {
    host: "0.0.0.0",
    port: 3000,
    strictPort: true, // Fail se porta occupata
    https:
      isDev || isPackageDev
        ? {
            // Per Laravel Herd, usiamo certificati self-signed
            // Puoi configurare certificati SSL personalizzati qui se necessario
          }
        : false,
    hmr: {
      host: "localhost",
    },
  },
  optimizeDeps: {
    include: [
      "vue",
      "pinia",
      "@heroicons/vue",
      "axios",
      "lodash-es",
      "@inertiajs/vue3",
      "@cartino/ui",
    ],
  },
});
