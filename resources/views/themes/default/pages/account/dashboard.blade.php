@extends('themes.default.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.account.dashboard') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.account.welcome_back', ['name' => $customer->first_name]) }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Account Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <nav class="space-y-1">
                        <a
                            href="{{ route('storefront.account.dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium bg-indigo-50 text-indigo-700 rounded-lg"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('storefront.account.dashboard') }}
                        </a>

                        <a
                            href="{{ route('storefront.account.orders') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors"
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

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Orders -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">{{ __('storefront.account.total_orders') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_orders'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">{{ __('storefront.account.total_spent') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ money($stats['total_spent']) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">{{ __('storefront.account.pending_orders') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_orders'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('storefront.account.recent_orders') }}</h2>
                        <a href="{{ route('storefront.account.orders') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            {{ __('storefront.account.view_all') }}
                        </a>
                    </div>

                    @if($recentOrders->count() > 0)
                        <div class="divide-y">
                            @foreach($recentOrders as $order)
                                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('storefront.account.orders.show', $order) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                                    #{{ $order->number }}
                                                </a>
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $order->status_color }}">
                                                    {{ $order->status_label }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ $order->created_at->format('M d, Y') }} • {{ $order->items_count }} {{ __('storefront.account.items') }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900">{{ money($order->total) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('storefront.account.no_orders') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('storefront.account.no_orders_description') }}</p>
                            <div class="mt-6">
                                <a href="{{ route('storefront.products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    {{ __('storefront.cart.continue_shopping') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Account Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.account.account_details') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('storefront.checkout.full_name') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('storefront.checkout.email') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->email }}</p>
                        </div>
                        @if($customer->phone)
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('storefront.checkout.phone') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->phone }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ __('storefront.account.member_since') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
