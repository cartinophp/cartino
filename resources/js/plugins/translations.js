/**
 * Statamic-style translations plugin for Vue
 * Registers translation helpers as global properties
 */

import { __, __choice, getLocale, hasTranslation } from '../translations'

export default {
  install(app) {
    // Global properties - available in all components
    // Options API: this.__('cp.save')
    // Template: {{ __('cp.save') }}
    app.config.globalProperties.__ = __
    app.config.globalProperties.__choice = __choice
    app.config.globalProperties.$t = __ // Alias
    app.config.globalProperties.$tc = __choice // Alias
    app.config.globalProperties.$locale = getLocale()
    app.config.globalProperties.$hasTranslation = hasTranslation

    // Provide for Composition API
    // Usage: const { __ } = inject('translations')
    app.provide('translations', {
      __,
      __choice,
      t: __,
      tc: __choice,
      locale: getLocale(),
      hasTranslation,
    })

    // Mixin to make __ available in <script setup> templates
    // This allows using __() directly in templates without import
    app.mixin({
      methods: {
        __,
        __choice,
      },
    })
  },
}
