@props(['transparent' => false])

<header
    x-data="{
        mobileMenuOpen: false,
        cartOpen: false,
        searchOpen: false,
        scrolled: false
    }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.pageYOffset > 20 })"
    :class="{ 'bg-white shadow-sm': scrolled || !{{ $transparent ? 'true' : 'false' }}, 'bg-transparent': !scrolled && {{ $transparent ? 'true' : 'false' }} }"
    class="sticky top-0 z-40 transition-all duration-300"
>
    <!-- Top Bar (Announcements) -->
    @if(setting('storefront.announcement_bar_enabled'))
        <div class="bg-indigo-600 px-4 py-2 text-center text-sm text-white">
            <p>{{ setting('storefront.announcement_text') }}</p>
        </div>
    @endif

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('storefront.home') }}" class="flex items-center">
                    @if(setting('storefront.logo'))
                        <img src="{{ setting('storefront.logo') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                    @else
                        <span class="text-xl font-bold text-gray-900">{{ config('app.name') }}</span>
                    @endif
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex lg:items-center lg:space-x-8">
                @foreach($mainMenu ?? [] as $item)
                    @if(!empty($item['children']))
                        <!-- Dropdown Menu -->
                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                            <button
                                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
                                :class="{ 'text-indigo-600': open }"
                            >
                                {{ $item['label'] }}
                                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Panel -->
                            <div
                                x-show="open"
                                x-transition
                                class="absolute left-0 mt-2 w-48 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
                            >
                                <div class="py-1">
                                    @foreach($item['children'] as $child)
                                        <a href="{{ $child['url'] }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Right Side Icons -->
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <button
                    @click="searchOpen = !searchOpen"
                    class="text-gray-700 hover:text-gray-900"
                    aria-label="{{ __('storefront.nav.search') }}"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                <!-- Account -->
                @auth('customer')
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button
                            @click="open = !open"
                            class="flex items-center text-gray-700 hover:text-gray-900"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-transition
                            class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
                        >
                            <div class="py-1">
                                <a href="{{ route('storefront.account.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('storefront.account.dashboard') }}
                                </a>
                                <a href="{{ route('storefront.account.orders') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('storefront.account.orders') }}
                                </a>
                                <form method="POST" action="{{ route('customer.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                        {{ __('storefront.account.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('customer.login') }}" class="text-gray-700 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                @endauth

                <!-- Cart -->
                <button
                    @click="cartOpen = true"
                    class="relative text-gray-700 hover:text-gray-900"
                    aria-label="{{ __('storefront.nav.cart') }}"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    @if(($cartItemsCount ?? 0) > 0)
                        <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-xs font-medium text-white">
                            {{ $cartItemsCount }}
                        </span>
                    @endif
                </button>

                <!-- Mobile Menu Button -->
                <button
                    @click="mobileMenuOpen = true"
                    class="lg:hidden text-gray-700 hover:text-gray-900"
                    aria-label="{{ __('storefront.nav.menu') }}"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search Bar (Expandable) -->
        <div
            x-show="searchOpen"
            x-transition
            class="pb-4 pt-2"
        >
            <form action="{{ route('storefront.search') }}" method="GET" class="relative">
                <input
                    type="text"
                    name="q"
                    placeholder="{{ __('storefront.search.placeholder') }}"
                    class="w-full rounded-lg border-gray-300 pl-10 pr-4 focus:border-indigo-500 focus:ring-indigo-500"
                >
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>
        </div>
    </div>
</header>

@push('scripts')
<script>
    // Handle cart count update via Alpine store
    document.addEventListener('alpine:init', () => {
        Alpine.store('cart', {
            count: {{ $cartItemsCount ?? 0 }},
            updateCount(newCount) {
                this.count = newCount;
            }
        });
    });
</script>
@endpush
