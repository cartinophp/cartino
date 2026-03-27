import { usePage } from "@inertiajs/vue3";

/**
 * Plugin di traduzione semplice per Inertia.js
 */
export const TranslationPlugin = {
  install(app) {
    // Traduzioni di fallback per test
    const fallbackTranslations = {
      cartino: {
        auth: {
          headings: {
            login: "Accedi al Control Panel",
          },
          descriptions: {
            login: "Inserisci le tue credenziali per accedere",
          },
          labels: {
            email: "Email",
            password: "Password",
            remember_me: "Ricordami",
            forgot_password: "Password dimenticata?",
            login: "Accedi",
          },
          placeholders: {
            email: "inserisci@email.com",
            password: "La tua password",
          },
          actions: {
            signing_in: "Accesso in corso...",
          },
        },
      },
    };

    // Funzione di traduzione globale
    const translate = (key, replacements = {}) => {
      try {
        // Ottieni la pagina corrente da Inertia
        const page = usePage();
        const translations = page.props.translations || fallbackTranslations;

        const keys = key.split(".");
        let translation = translations;

        for (const k of keys) {
          if (
            translation &&
            typeof translation === "object" &&
            k in translation
          ) {
            translation = translation[k];
          } else {
            return key; // Return key if translation not found
          }
        }

        if (typeof translation !== "string") {
          return key;
        }

        // Replace placeholders like :name
        return translation.replace(/:(\w+)/g, (match, key) => {
          return replacements[key] !== undefined ? replacements[key] : match;
        });
      } catch (error) {
        console.warn("Translation error:", error);
        return key;
      }
    };

    // Registra come proprietà globale
    app.config.globalProperties.$t = translate;

    // Provide per composables
    app.provide("translate", translate);
  },
};
