<template>
  <AuthLayout 
    :title="t('cartino::auth.titles.reset_password')" 
    v-bind="props"
  >
    <!-- Reset Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Email Field (Hidden) -->
        <input type="hidden" v-model="form.token" />
        <input type="hidden" v-model="form.email" />

        <!-- Email Display -->
        <div>
          <label
            class="block text-sm font-medium leading-6 text-gray-900 dark:text-white"
          >
            {{ t("cartino::auth.labels.email") }}
          </label>
          <div class="mt-2">
            <div
              class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-500 bg-gray-50 ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6"
            >
              {{ email }}
            </div>
          </div>
        </div>

        <!-- Password Field -->
        <div>
          <label
            for="password"
            class="block text-sm font-medium leading-6 text-gray-900 dark:text-white"
          >
            {{ t("cartino::auth.labels.password") }}
          </label>
          <div class="mt-2">
            <input
              id="password"
              v-model="form.password"
              type="password"
              :placeholder="t('cartino::auth.placeholders.password')"
              required
              autocomplete="new-password"
              autofocus
              :class="[
                'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                errors.password
                  ? 'ring-red-300 focus:ring-red-600'
                  : 'ring-gray-300',
              ]"
            />
            <div v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password }}
            </div>
          </div>
        </div>

        <!-- Password Confirmation Field -->
        <div>
          <label
            for="password_confirmation"
            class="block text-sm font-medium leading-6 text-gray-900 dark:text-white"
          >
            {{ t("cartino::auth.labels.password_confirmation") }}
          </label>
          <div class="mt-2">
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              :placeholder="
                t('cartino::auth.placeholders.password_confirmation')
              "
              required
              autocomplete="new-password"
              :class="[
                'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                errors.password_confirmation
                  ? 'ring-red-300 focus:ring-red-600'
                  : 'ring-gray-300',
              ]"
            />
            <div
              v-if="errors.password_confirmation"
              class="mt-1 text-sm text-red-600"
            >
              {{ errors.password_confirmation }}
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div>
          <button
            type="submit"
            :disabled="form.processing"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              {{ t("cartino::auth.actions.resetting_password") }}
            </span>
            <span v-else>
              {{ t("cartino::auth.labels.reset_password") }}
            </span>
          </button>
        </div>

        <!-- Back to Login -->
        <div class="text-center">
          <Link
            :href="route('cp.login')"
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-500"
          >
            {{ t("cartino.auth.labels.back_to_login") }}
          </Link>
        </div>
      </form>
  </AuthLayout>
</template>

<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import { useTranslations } from "@/composables/useTranslations";
import AuthLayout from '@/layouts/auth-layout.vue'

// Auth layout per pagine di autenticazione
defineOptions({
  layout: AuthLayout
});

// Props
const props = defineProps({
  token: String,
  email: String,
  locale: String,
  locales: Array,
  app_name: String,
  cp_name: String,
  branding: Object,
  errors: Object,
});

// Route helper
const route = (name, params = {}, absolute = true) => {
    return window.route ? window.route(name, params, absolute) : '#'
}

// Composables
const { t } = useTranslations();

// Form
const form = useForm({
  token: props.token,
  email: props.email,
  password: "",
  password_confirmation: "",
});

// Methods
const submit = () => {
  form.post(route("cp.password.store"), {
    onFinish: () => {
      form.reset("password", "password_confirmation");
    },
  });
};

const changeLocale = (locale) => {
  window.location.href = `${window.location.pathname}?locale=${locale}`;
};
</script>

<style scoped>
/* Dark mode styles */
@media (prefers-color-scheme: dark) {
  .dark\:text-white {
    color: #ffffff;
  }

  .dark\:text-gray-400 {
    color: #9ca3af;
  }
}
</style>
