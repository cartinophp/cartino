@extends('themes.default.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.account.orders') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.account.orders_description') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Account Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <nav class="space-y-1">
                        <a
                            href="{{ route('storefront.account.dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('storefront.account.dashboard') }}
                        </a>

                        <a
                            href="{{ route('storefront.account.orders') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium bg-indigo-50 text-indigo-700 rounded-lg"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            {{ __('storefront.account.orders') }}
                        </a>

                        <a
                            href="{{ route('storefront.account.addresses') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('storefront.account.addresses') }}
                        </a>

                        <a
                            href="{{ route('storefront.account.settings') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('storefront.account.settings') }}
                        </a>

                        <form action="{{ route('storefront.logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="w-full flex items-center px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('storefront.account.logout') }}
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Orders List -->
            <div class="lg:col-span-2">
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6" x-data="{ showFilters: false }">
                    <div class="flex items-center justify-between">
                        <button
                            @click="showFilters = !showFilters"
                            class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            {{ __('storefront.filters.filters') }}
                        </button>
                        <span class="text-sm text-gray-500">{{ $orders->total() }} {{ __('storefront.account.orders') }}</span>
                    </div>

                    <div x-show="showFilters" x-transition class="mt-4 pt-4 border-t">
                        <form method="GET" action="{{ route('storefront.account.orders') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.order_status') }}</label>
                                <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">{{ __('storefront.filters.all') }}</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('storefront.order.status_pending') }}</option>
                                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('storefront.order.status_processing') }}</option>
                                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>{{ __('storefront.order.status_shipped') }}</option>
                                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>{{ __('storefront.order.status_delivered') }}</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('storefront.order.status_cancelled') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.filters.date_range') }}</label>
                                <select name="date_range" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">{{ __('storefront.filters.all_time') }}</option>
                                    <option value="30" {{ request('date_range') === '30' ? 'selected' : '' }}>{{ __('storefront.filters.last_30_days') }}</option>
                                    <option value="90" {{ request('date_range') === '90' ? 'selected' : '' }}>{{ __('storefront.filters.last_90_days') }}</option>
                                    <option value="365" {{ request('date_range') === '365' ? 'selected' : '' }}>{{ __('storefront.filters.last_year') }}</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                                    {{ __('storefront.filters.apply') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders -->
                @if($orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($orders as $order)
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <!-- Order Header -->
                                <div class="px-6 py-4 border-b bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div>
                                                <a href="{{ route('storefront.account.orders.show', $order) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                                    {{ __('storefront.account.order_number', ['number' => $order->number]) }}
                                                </a>
                                                <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $order->status_color }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900">{{ money($order->total) }}</p>
                                            <p class="text-xs text-gray-500">{{ $order->items_count }} {{ __('storefront.account.items') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="px-6 py-4">
                                    <div class="space-y-3">
                                        @foreach($order->lines->take(3) as $line)
                                            <div class="flex gap-4">
                                                <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded overflow-hidden">
                                                    @if($line->product->getFirstMediaUrl('images'))
                                                        <img
                                                            src="{{ $line->product->getFirstMediaUrl('images', 'thumb') }}"
                                                            alt="{{ $line->product->name }}"
                                                            class="w-full h-full object-cover"
                                                        >
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">{{ $line->product->name }}</p>
                                                    @if($line->variant)
                                                        <p class="text-xs text-gray-500">{{ $line->variant->name }}</p>
                                                    @endif
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('storefront.product.quantity') }}: {{ $line->quantity }}</p>
                                                </div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ money($line->total) }}
                                                </div>
                                            </div>
                                        @endforeach

                                        @if($order->lines->count() > 3)
                                            <p class="text-sm text-gray-500">
                                                {{ __('storefront.account.and_more_items', ['count' => $order->lines->count() - 3]) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Order Actions -->
                                <div class="px-6 py-4 border-t bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('storefront.account.orders.show', $order) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                            {{ __('storefront.account.view_order') }} →
                                        </a>
                                        <div class="flex gap-2">
                                            @if($order->canTrack())
                                                <a href="{{ route('storefront.account.orders.track', $order) }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                    {{ __('storefront.account.track_order') }}
                                                </a>
                                            @endif
                                            @if($order->canDownloadInvoice())
                                                <a href="{{ route('storefront.account.orders.invoice', $order) }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                    {{ __('storefront.account.download_invoice') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($orders->hasPages())
                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ __('storefront.account.no_orders') }}</h2>
                        <p class="mt-2 text-gray-500">{{ __('storefront.account.no_orders_description') }}</p>
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
    </div>
</div>
@endsection
