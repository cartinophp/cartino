import { usePage } from "@inertiajs/vue3";

/**
 * Composable per gestire le traduzioni tramite Inertia shared data
 */
export function useTranslations() {
  const page = usePage();

  const t = (key, replacements = {}) => {
    const translations = page.props.translations || {};
    const keys = key.split(".");
    let translation = translations;

    for (const k of keys) {
      if (translation && typeof translation === "object" && k in translation) {
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
  };

  const tc = (key, count, replacements = {}) => {
    // Simple pluralization
    const translation = t(key, { ...replacements, count });

    if (count === 1) {
      return translation.split("|")[0] || translation;
    } else {
      return translation.split("|")[1] || translation;
    }
  };

  const locale = () => {
    return page.props.locale || "en";
  };

  const locales = () => {
    return page.props.locales || [];
  };

  return {
    t,
    tc,
    locale,
    locales,
  };
}

/**
 * Helper function per formattare valuta
 */
export function formatCurrency(amount, currency = "EUR", locale = "it-IT") {
  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency: currency,
  }).format(amount);
}

/**
 * Helper function per formattare date
 */
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

/**
 * Helper function per formattare tempo relativo
 */
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
