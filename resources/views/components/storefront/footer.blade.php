<footer class="bg-gray-900 text-gray-300">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
            <!-- Company Info -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-white">{{ config('app.name') }}</h3>
                <p class="text-sm">
                    {{ setting('storefront.footer_description', 'Your trusted online store for quality products.') }}
                </p>

                <!-- Social Links -->
                <div class="flex space-x-4">
                    @if($facebook = setting('social.facebook'))
                        <a href="{{ $facebook }}" target="_blank" rel="noopener" class="hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    @endif

                    @if($instagram = setting('social.instagram'))
                        <a href="{{ $instagram }}" target="_blank" rel="noopener" class="hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    @endif

                    @if($twitter = setting('social.twitter'))
                        <a href="{{ $twitter }}" target="_blank" rel="noopener" class="hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">
                    {{ __('storefront.footer.customer_service') }}
                </h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('storefront.pages.show', 'shipping-info') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.shipping_info') }}</a></li>
                    <li><a href="{{ route('storefront.pages.show', 'returns') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.returns') }}</a></li>
                    <li><a href="{{ route('storefront.pages.show', 'faq') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.faq') }}</a></li>
                    <li><a href="{{ route('storefront.pages.show', 'contact') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.contact_us') }}</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">
                    {{ __('storefront.footer.about_us') }}
                </h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('storefront.pages.show', 'about') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.about_us') }}</a></li>
                    <li><a href="{{ route('storefront.blog.index') }}" class="hover:text-white transition-colors">{{ __('storefront.nav.blog') }}</a></li>
                    <li><a href="{{ route('storefront.pages.show', 'privacy') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.privacy_policy') }}</a></li>
                    <li><a href="{{ route('storefront.pages.show', 'terms') }}" class="hover:text-white transition-colors">{{ __('storefront.footer.terms_conditions') }}</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">
                    {{ __('storefront.footer.newsletter') }}
                </h3>
                <p class="mb-4 text-sm">{{ __('storefront.footer.newsletter_text') }}</p>

                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="space-y-2">
                    @csrf
                    <input
                        type="email"
                        name="email"
                        placeholder="{{ __('storefront.footer.newsletter_placeholder') }}"
                        required
                        class="w-full rounded-md border-gray-700 bg-gray-800 px-4 py-2 text-sm text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <button
                        type="submit"
                        class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                    >
                        {{ __('storefront.footer.subscribe') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-8 border-t border-gray-800 pt-8">
            <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                <!-- Copyright -->
                <p class="text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('storefront.footer.all_rights_reserved') }}
                </p>

                <!-- Payment Methods -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm">{{ __('storefront.footer.payment_methods') }}:</span>
                    <div class="flex space-x-2" role="list" aria-label="Accepted payment methods">
                        <svg class="h-8 w-12" viewBox="0 0 48 32" fill="none" role="img" aria-label="Mastercard">
                            <rect width="48" height="32" rx="4" fill="#1434CB"/>
                            <path d="M20.5 11h7v10h-7z" fill="#FF5F00"/>
                            <path d="M21 16a6.5 6.5 0 0112 0 6.5 6.5 0 01-12 0z" fill="#EB001B"/>
                            <path d="M27 16a6.5 6.5 0 0113 0 6.5 6.5 0 01-13 0z" fill="#F79E1B"/>
                        </svg>
                        <svg class="h-8 w-12" viewBox="0 0 48 32" fill="none" role="img" aria-label="American Express">
                            <rect width="48" height="32" rx="4" fill="#0066B2"/>
                            <path fill="#ffffff" d="M18 12l-3 8h2l.5-1.5h3L21 20h2l-3-8h-2zm.8 5.5l1-3 1 3h-2z"/>
                        </svg>
                        <svg class="h-8 w-12" viewBox="0 0 48 32" fill="none" role="img" aria-label="Visa">
                            <rect width="48" height="32" rx="4" fill="#00579F"/>
                            <path fill="#FAA61A" d="M15 11h18v10H15z"/>
                        </svg>
                    </div>
                </div>

                <!-- Language Switcher -->
                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        @click.away="open = false"
                        class="flex items-center space-x-2 text-sm hover:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                    </button>

                    <div
                        x-show="open"
                        x-transition
                        class="absolute bottom-full right-0 mb-2 w-32 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
                    >
                        @foreach(config('cartino.locales') as $locale => $label)
                            <a
                                href="{{ route('locale.switch', $locale) }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === $locale ? 'bg-gray-50 font-medium' : '' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
