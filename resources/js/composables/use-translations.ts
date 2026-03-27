/**
 * Vue composable for translations (Statamic-style)
 */

import { __, __choice, getLocale, hasTranslation } from '../translations'

export function useTranslations() {
  return {
    /**
     * Translate a string
     */
    __,

    /**
     * Translate with pluralization
     */
    __choice,

    /**
     * Get current locale
     */
    locale: getLocale(),

    /**
     * Check if translation exists
     */
    hasTranslation,

    /**
     * Translate (alias for template usage)
     */
    t: __,

    /**
     * Translate with count (alias for template usage)
     */
    tc: __choice,
  }
}

// Export as default for easy importing
export default useTranslations
