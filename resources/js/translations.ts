/**
 * Statamic-style translation helper for JS
 * Accesses translations from window.CartinoConfig.translations
 */

interface Replacements {
  [key: string]: string | number
}

declare global {
  interface Window {
    CartinoConfig: {
      locale: string
      translations: Record<string, any>
      csrf_token: string
      app_url: string
      timezone: string
      currency: string
    }
  }
}

/**
 * Get a translation string from the loaded translations
 * 
 * @param key - Translation key in dot notation (e.g., 'cp.save', 'validation.required')
 * @param replacements - Object with replacements for :placeholder syntax
 * @returns Translated string or the key if not found
 * 
 * @example
 * __('cp.save') // Returns: 'Save'
 * __('validation.required', { attribute: 'name' }) // Returns: 'The name field is required'
 */
export function __(key: string, replacements?: Replacements): string {
  // Get translations from window.CartinoConfig
  const translations = window.CartinoConfig?.translations ?? {}

  // Split the key by dots
  const keys = key.split('.')
  
  // Traverse the object to find the translation
  let value: any = translations
  for (const k of keys) {
    if (value && typeof value === 'object' && k in value) {
      value = value[k]
    } else {
      // Translation not found, return the key
      return key
    }
  }

  // If value is not a string, return the key
  if (typeof value !== 'string') {
    return key
  }

  // Replace placeholders if replacements are provided
  if (replacements) {
    for (const [placeholder, replacement] of Object.entries(replacements)) {
      // Replace :placeholder and :PLACEHOLDER formats
      value = value.replace(
        new RegExp(`:${placeholder}`, 'gi'),
        String(replacement)
      )
    }
  }

  return value
}

/**
 * Get the current locale
 */
export function getLocale(): string {
  return window.CartinoConfig?.locale ?? 'en'
}

/**
 * Check if a translation exists
 */
export function hasTranslation(key: string): boolean {
  const translations = window.CartinoConfig?.translations ?? {}
  const keys = key.split('.')
  
  let value: any = translations
  for (const k of keys) {
    if (value && typeof value === 'object' && k in value) {
      value = value[k]
    } else {
      return false
    }
  }

  return typeof value === 'string'
}

/**
 * Get a translation choice based on count (pluralization)
 * 
 * @param key - Translation key
 * @param count - Number to determine singular/plural
 * @param replacements - Replacements including :count
 * 
 * @example
 * __choice('cp.items_count', 1) // '1 item'
 * __choice('cp.items_count', 5) // '5 items'
 */
export function __choice(
  key: string,
  count: number,
  replacements?: Replacements
): string {
  const translation = __(key, { count, ...replacements })
  
  // Simple pluralization: look for | separator
  if (translation.includes('|')) {
    const [singular, plural] = translation.split('|')
    return count === 1 ? singular.trim() : plural.trim()
  }

  return translation
}

// Export a default object with all translation utilities
export default {
  __,
  getLocale,
  hasTranslation,
  __choice,
}
