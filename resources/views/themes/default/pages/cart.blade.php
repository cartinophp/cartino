@extends('themes.default.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="mb-8 flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="{{ route('storefront.home') }}" class="hover:text-gray-700">{{ __('storefront.nav.home') }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900">{{ __('storefront.cart.title') }}</li>
            </ol>
        </nav>

        <h1 class="mb-8 text-3xl font-bold text-gray-900">{{ __('storefront.cart.title') }}</h1>

        @if($cart && $cart->lines->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6 space-y-4">
                            @foreach($cart->lines as $line)
                                <div class="flex gap-4 pb-4 border-b last:border-b-0" x-data="{ quantity: {{ $line->quantity }} }">
                                    <!-- Product Image -->
                                    <a href="{{ route('storefront.products.show', $line->product->handle) }}" class="flex-shrink-0">
                                        <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                                            @if($line->product->getFirstMediaUrl('images'))
                                                <img
                                                    src="{{ $line->product->getFirstMediaUrl('images', 'thumb') }}"
                                                    alt="{{ $line->product->name }}"
                                                    class="w-full h-full object-cover"
                                                >
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </a>

                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('storefront.products.show', $line->product->handle) }}" class="text-base font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $line->product->name }}
                                        </a>

                                        @if($line->variant)
                                            <p class="mt-1 text-sm text-gray-500">{{ $line->variant->name }}</p>
                                        @endif

                                        <!-- Price -->
                                        <p class="mt-2 text-lg font-semibold text-gray-900">
                                            {{ money($line->price) }}
                                        </p>

                                        <!-- Quantity & Remove -->
                                        <div class="mt-4 flex items-center gap-4">
                                            <!-- Quantity Selector -->
                                            <form action="{{ route('storefront.cart.update', $line->id) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')

                                                <label class="text-sm text-gray-600">{{ __('storefront.product.quantity') }}:</label>

                                                <div class="flex items-center border border-gray-300 rounded-lg">
                                                    <button
                                                        type="button"
                                                        @click="quantity = Math.max(1, quantity - 1)"
                                                        aria-label="{{ __('storefront.cart.decrease_quantity') }}"
                                                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                        </svg>
                                                    </button>

                                                    <input
                                                        type="number"
                                                        name="quantity"
                                                        x-model="quantity"
                                                        aria-label="{{ __('storefront.product.quantity') }}"
                                                        min="1"
                                                        max="99"
                                                        class="w-16 text-center border-0 focus:ring-0 focus:outline-none"
                                                    >

                                                    <button
                                                        type="button"
                                                        @click="quantity = Math.min(99, quantity + 1)"
                                                        aria-label="{{ __('storefront.cart.increase_quantity') }}"
                                                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                                    {{ __('storefront.cart.update') }}
                                                </button>
                                            </form>

                                            <!-- Remove -->
                                            <form action="{{ route('storefront.cart.remove', $line->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                                    {{ __('storefront.cart.remove') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Line Total -->
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ money($line->total) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Continue Shopping -->
                    <a href="{{ route('storefront.products.index') }}" class="mt-6 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('storefront.cart.continue_shopping') }}
                    </a>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.checkout.order_summary') }}</h2>

                        <!-- Coupon Code -->
                        <form action="{{ route('storefront.cart.apply-coupon') }}" method="POST" class="mb-6">
                            @csrf
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('storefront.cart.coupon_code') }}
                            </label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    name="code"
                                    placeholder="SAVE10"
                                    class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                                    {{ __('storefront.cart.apply_coupon') }}
                                </button>
                            </div>
                        </form>

                        <!-- Summary Lines -->
                        <div class="space-y-3 border-t pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.subtotal') }}</span>
                                <span class="font-medium text-gray-900">{{ money($cart->subtotal) }}</span>
                            </div>

                            @if($cart->discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('storefront.cart.discount') }}</span>
                                    <span class="font-medium text-green-600">-{{ money($cart->discount) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.shipping') }}</span>
                                <span class="font-medium text-gray-900">
                                    @if($cart->shipping > 0)
                                        {{ money($cart->shipping) }}
                                    @else
                                        <span class="text-green-600">{{ __('storefront.product.free_shipping') }}</span>
                                    @endif
                                </span>
                            </div>

                            @if($cart->tax > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('storefront.cart.tax') }}</span>
                                    <span class="font-medium text-gray-900">{{ money($cart->tax) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-base font-semibold border-t pt-3">
                                <span class="text-gray-900">{{ __('storefront.cart.total') }}</span>
                                <span class="text-gray-900">{{ money($cart->total) }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <a
                            href="{{ route('storefront.checkout') }}"
                            class="mt-6 block w-full bg-indigo-600 text-white text-center rounded-lg px-6 py-3 font-medium hover:bg-indigo-700 transition-colors"
                        >
                            {{ __('storefront.cart.checkout') }}
                        </a>

                        <!-- Security Badge -->
                        <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>{{ __('storefront.cart.secure_checkout') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ __('storefront.cart.empty') }}</h2>
                <p class="mt-2 text-gray-500">{{ __('storefront.cart.empty_description') }}</p>
                <a
                    href="{{ route('storefront.products.index') }}"
                    class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    {{ __('storefront.cart.continue_shopping') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
