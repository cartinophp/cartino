<template>
    <AuthLayout :title="t('cartino.auth.titles.login')" v-bind="props">
        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                    {{ t('cartino.auth.labels.email') }}
                </label>
                <div class="mt-2">
                    <input id="email" v-model="form.email" type="email"
                        :placeholder="t('cartino.auth.placeholders.email')" required autocomplete="email" :class="[
                            'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                            props.errors.email ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300'
                        ]" />
                    <div v-if="props.errors.email" class="mt-1 text-sm text-red-600">
                        {{ props.errors.email }}
                    </div>
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                    {{ t('cartino.auth.labels.password') }}
                </label>
                <div class="mt-2">
                    <input id="password" v-model="form.password" type="password"
                        :placeholder="t('cartino.auth.placeholders.password')" required autocomplete="current-password"
                        :class="[
                            'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                            props.errors.password ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300'
                        ]" />
                    <div v-if="props.errors.password" class="mt-1 text-sm text-red-600">
                        {{ props.errors.password }}
                    </div>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" v-model="form.remember" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        {{ t('cartino.auth.labels.remember_me') }}
                    </label>
                </div>

                <div class="text-sm" v-if="props.canResetPassword">
                    <Link :href="route('cp.password.request')"
                        class="font-semibold text-indigo-600 hover:text-indigo-500">
                        {{ t('cartino.auth.labels.forgot_password') }}
                    </Link>
                </div>
            </div>


            <button type="submit" :disabled="form.processing" :loading="form.processing" class="w-full justify-center">


                {{ t('cartino.auth.labels.login') }}

            </button>

        </form>
    </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import { useTranslations } from '@/composables/useTranslations'
import AuthLayout from '@/layouts/auth-layout.vue'

// Auth layout per pagine di autenticazione
defineOptions({
    layout: AuthLayout
})

// Route helper
const route = (name, params = {}, absolute = true) => {
    return window.route ? window.route(name, params, absolute) : '#'
}

// Props
const props = defineProps({
    status: String,
    canResetPassword: Boolean,
    locale: String,
    locales: {
        type: Array,
        default: () => []
    },
    app_name: String,
    cp_name: String,
    branding: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    },
})

// Translations
const { t } = useTranslations()

// Form
const form = useForm({
    email: '',
    password: '',
    remember: false,
})

// Methods
const submit = () => {
    form.post(route('cp.login'), {
        onFinish: () => {
            form.reset('password')
        },
    })
}

const changeLocale = (locale) => {
    // This would typically make a request to change locale
    window.location.href = `${window.location.pathname}?locale=${locale}`
}
</script>
