import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import CpLayout from "@/layouts/cp-layout.vue";

// Import Cartino configuration fallbacks
import { defaultCartinoConfig } from "@/config/cartino-config.js";

// Import translation plugin (Statamic-style)
import translationsPlugin from "@/plugins/translations";

// Import global styles
import "../css/app.css";

// Configure CSRF token for requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.Laravel = {
        csrfToken: token.content
    };
}

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob("./pages/**/*.vue", { eager: true });
    const page = pages[`./pages/${name}.vue`];
    
    // Set default layout only if not already specified and not an auth page
    if (!page.default.layout && !name.startsWith('auth/')) {
      page.default.layout = CpLayout;
    }
    
    return page;
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      .use(translationsPlugin); // Statamic-style translations

    // Configure CSRF token for Inertia requests
    if (window.Laravel && window.Laravel.csrfToken) {
      app.config.globalProperties.$csrf = window.Laravel.csrfToken;
    }

    // Global Properties - ensure CartinoConfig has all required properties
    const cartinoConfig = window.CartinoConfig || defaultCartinoConfig;
    cartinoConfig.translations = cartinoConfig.translations || {};
    app.config.globalProperties.$cartinoConfig = cartinoConfig;

    // Error Handler
    app.config.errorHandler = (err, vm, info) => {
      console.error("Vue Error:", err, info);

      // Send to error reporting service
      if (window.Sentry) {
        window.Sentry.captureException(err, {
          contexts: {
            vue: {
              componentName: vm?.$options?.name,
              info: info,
            },
          },
        });
      }
    };

    return app.mount(el);
  },
});

// Export for debugging
if (import.meta.env.DEV) {
  window.CartinoApp = app;
}
