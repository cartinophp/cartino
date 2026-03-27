import { defineStore } from "pinia";
import { computed, ref, watch } from "vue";
import { Translator } from "../utils/translator";

export const useLocaleStore = defineStore("locale", () => {
  // Debug CartinoConfig
  if (typeof window !== 'undefined' && window.CartinoConfig) {
    console.log('CartinoConfig loaded:', window.CartinoConfig);
  } else {
    console.warn('CartinoConfig not found, using defaults');
  }

  // State
  const currentLocale = ref(window.CartinoConfig?.locale || "it");
  const availableLocales = ref(
    window.CartinoConfig?.availableLocales || { en: "English", it: "Italiano" },
  );
  const translations = ref(window.CartinoConfig?.translations || {});
  const translator = ref(
    new Translator(translations.value, currentLocale.value),
  );

  // Computed
  const localeOptions = computed(() => {
    return Object.entries(availableLocales.value).map(([code, name]) => ({
      value: code,
      label: name,
    }));
  });

  // Actions
  const setLocale = async (locale) => {
    if (!availableLocales.value[locale]) {
      console.warn(`Locale ${locale} is not available`);
      return;
    }

    const oldLocale = currentLocale.value;
    currentLocale.value = locale;

    try {
      // Load new translations if not cached
      if (
        !translations.value[locale] ||
        Object.keys(translations.value[locale]).length === 0
      ) {
        await loadTranslations(locale);
      }

      // Update translator
      translator.value.setLocale(locale);

      // Persist locale preference
      localStorage.setItem("cartino_locale", locale);

      // Update document lang attribute
      document.documentElement.lang = locale;

      // Send to backend to update user preference
      if (window.CartinoConfig?.user) {
        try {
          await fetch("/cp/user/locale", {
            method: "PATCH",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content"),
            },
            body: JSON.stringify({ locale }),
          });
        } catch (error) {
          console.warn("Failed to update user locale preference:", error);
        }
      }
    } catch (error) {
      console.error("Failed to set locale:", error);
      currentLocale.value = oldLocale;
      throw error;
    }
  };

  const loadTranslations = async (locale) => {
    try {
      const response = await fetch(`/cp/translations/${locale}`);

      if (!response.ok) {
        throw new Error(`Failed to load translations for ${locale}`);
      }

      const newTranslations = await response.json();
      translations.value[locale] = newTranslations;

      return newTranslations;
    } catch (error) {
      console.error("Failed to load translations:", error);
      throw error;
    }
  };

  const t = (key, replacements = {}) => {
    return translator.value.get(key, replacements);
  };

  const tc = (key, count, replacements = {}) => {
    return translator.value.choice(key, count, replacements);
  };

  const te = (key) => {
    return translator.value.has(key);
  };

  const getNamespace = (namespace) => {
    return translator.value.getNamespace(namespace);
  };

  // Initialize locale from localStorage or browser
  const initializeLocale = () => {
    const savedLocale = localStorage.getItem("cartino_locale");

    if (savedLocale && availableLocales.value[savedLocale]) {
      setLocale(savedLocale).catch(console.warn);
    } else {
      // Try to detect from browser
      const browserLocale = navigator.language.split("-")[0];
      if (availableLocales.value[browserLocale]) {
        setLocale(browserLocale).catch(console.warn);
      }
    }
  };

  // Watch for translations changes
  watch(
    translations,
    (newTranslations) => {
      translator.value = new Translator(newTranslations, currentLocale.value);
    },
    { deep: true },
  );

  // Watch for current locale changes
  watch(currentLocale, (newLocale) => {
    translator.value.setLocale(newLocale);
  });

  return {
    // State
    currentLocale: computed(() => currentLocale.value),
    availableLocales: computed(() => availableLocales.value),
    translations: computed(() => translations.value),
    localeOptions,

    // Actions
    setLocale,
    loadTranslations,
    initializeLocale,

    // Translation methods
    t,
    tc,
    te,
    getNamespace,

    // Translator instance (for advanced usage)
    translator: computed(() => translator.value),
  };
});

// Composable for components
export function useTranslation() {
  const store = useLocaleStore();

  return {
    locale: store.currentLocale,
    availableLocales: store.availableLocales,
    localeOptions: store.localeOptions,
    setLocale: store.setLocale,
    t: store.t,
    tc: store.tc,
    te: store.te,
    getNamespace: store.getNamespace,
  };
}

// Format helpers
export function formatCurrency(amount, currency = "EUR", locale = "it-IT") {
  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency: currency,
  }).format(amount);
}

export function formatDate(date, options = {}, locale = "it-IT") {
  const defaultOptions = {
    year: "numeric",
    month: "long",
    day: "numeric",
  };

  return new Intl.DateTimeFormat(locale, {
    ...defaultOptions,
    ...options,
  }).format(new Date(date));
}

export function formatRelativeTime(date, locale = "it-IT") {
  const rtf = new Intl.RelativeTimeFormat(locale, { numeric: "auto" });
  const now = new Date();
  const target = new Date(date);
  const diff = target.getTime() - now.getTime();

  const units = [
    { unit: "year", ms: 31536000000 },
    { unit: "month", ms: 2628000000 },
    { unit: "day", ms: 86400000 },
    { unit: "hour", ms: 3600000 },
    { unit: "minute", ms: 60000 },
    { unit: "second", ms: 1000 },
  ];

  for (const { unit, ms } of units) {
    if (Math.abs(diff) >= ms) {
      return rtf.format(Math.round(diff / ms), unit);
    }
  }

  return rtf.format(0, "second");
}
