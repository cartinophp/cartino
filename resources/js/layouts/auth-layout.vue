<template>
  <Head :title="title" />
  <div class="max-h-screen flex items-center justify-center px-6 py-12 bg-gray-50">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-6">
        <img 
          v-if="branding?.logo" 
          :src="branding.logo" 
          :alt="app_name" 
          class="h-10 w-auto mx-auto"
        >
        <div v-else class="text-xl font-bold text-gray-900">
          {{ cp_name || app_name || 'Cartino' }}
        </div>
      </div>

      <!-- Card -->
      <div class="bg-whiterounded-lg px-6 py-8">
        <h2 class="text-xl font-semibold text-center text-gray-900 mb-6">
          <slot name="title">{{ title }}</slot>
        </h2>

        <div v-if="status" class="mb-4 p-3 rounded-md bg-green-50 border border-green-200">
          <p class="text-sm text-green-600">{{ status }}</p>
        </div>

        <slot />
      </div>
    </div>

      <!-- Language selector (se multilingua) -->
      <div v-if="locales && locales.length > 1" class="mt-6">
        <div class="flex justify-center space-x-2">
          <button
            v-for="loc in locales"
            :key="loc.code"
            @click="changeLocale(loc.code)"
            :class="[
              'px-2 py-1 text-xs rounded transition-colors',
              locale === loc.code 
                ? 'bg-indigo-600 text-white' 
                : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            {{ loc.name }}
          </button>
        </div>
      </div>
  </div>
</template>

<script setup>
import { usePage, Head } from '@inertiajs/vue3'

const page = usePage()

// Props dal controller/pagina
const props = defineProps({
  title: String,
  subtitle: String,
  status: String,
  locale: String,
  locales: Array,
  app_name: String,
  cp_name: String,
  branding: Object,
})

// Metodo per cambiare lingua
const changeLocale = (locale) => {
  window.location.href = `${window.location.pathname}?locale=${locale}`
}
</script>

<style scoped>
/* Custom styles per l'auth layout se necessario */
</style>