@extends('themes.default.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="mb-8 flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="{{ route('storefront.home') }}" class="hover:text-gray-700">{{ __('storefront.nav.home') }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('storefront.cart.show') }}" class="hover:text-gray-700">{{ __('storefront.cart.title') }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900">{{ __('storefront.checkout.title') }}</li>
            </ol>
        </nav>

        <h1 class="mb-8 text-3xl font-bold text-gray-900">{{ __('storefront.checkout.title') }}</h1>

        <div
            x-data="{
                currentStep: 1,
                formData: {
                    email: '{{ auth('customers')->user()?->email ?? '' }}',
                    billing: {
                        first_name: '{{ auth('customers')->user()?->first_name ?? '' }}',
                        last_name: '{{ auth('customers')->user()?->last_name ?? '' }}',
                        company: '',
                        address_line_1: '',
                        address_line_2: '',
                        city: '',
                        state: '',
                        postal_code: '',
                        country: 'US',
                        phone: '{{ auth('customers')->user()?->phone ?? '' }}'
                    },
                    shipping: {
                        same_as_billing: true,
                        first_name: '',
                        last_name: '',
                        company: '',
                        address_line_1: '',
                        address_line_2: '',
                        city: '',
                        state: '',
                        postal_code: '',
                        country: 'US',
                        phone: ''
                    },
                    shipping_method: '',
                    payment_method: '',
                    save_info: {{ auth('customers')->check() ? 'false' : 'true' }},
                    notes: ''
                },
                shippingMethods: @js($shippingMethods ?? []),
                paymentMethods: @js($paymentMethods ?? []),

                goToStep(step) {
                    if (step < this.currentStep || this.canProceed()) {
                        this.currentStep = step;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                canProceed() {
                    if (this.currentStep === 1) {
                        return this.formData.email &&
                               this.formData.billing.first_name &&
                               this.formData.billing.last_name &&
                               this.formData.billing.address_line_1 &&
                               this.formData.billing.city &&
                               this.formData.billing.postal_code;
                    }
                    if (this.currentStep === 2) {
                        return this.formData.shipping_method !== '';
                    }
                    return true;
                },

                async submitOrder() {
                    try {
                        const response = await fetch('{{ route('storefront.checkout.process') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert(data.message || '{{ __('storefront.messages.error_processing_order') }}');
                        }
                    } catch (error) {
                        console.error('Error submitting order:', error);
                        alert('{{ __('storefront.messages.error_processing_order') }}');
                    }
                }
            }"
        >
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <!-- Progress Steps -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <!-- Step 1 -->
                            <div class="flex items-center flex-1">
                                <button
                                    @click="goToStep(1)"
                                    :class="currentStep === 1 ? 'bg-indigo-600 text-white' : (currentStep > 1 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600')"
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-semibold"
                                >
                                    <span x-show="currentStep <= 1">1</span>
                                    <svg x-show="currentStep > 1" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <span class="ml-3 text-sm font-medium" :class="currentStep >= 1 ? 'text-gray-900' : 'text-gray-500'">
                                    {{ __('storefront.checkout.step_shipping') }}
                                </span>
                            </div>

                            <div class="w-16 h-0.5" :class="currentStep > 1 ? 'bg-green-600' : 'bg-gray-200'"></div>

                            <!-- Step 2 -->
                            <div class="flex items-center flex-1">
                                <button
                                    @click="goToStep(2)"
                                    :class="currentStep === 2 ? 'bg-indigo-600 text-white' : (currentStep > 2 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600')"
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-semibold"
                                >
                                    <span x-show="currentStep <= 2">2</span>
                                    <svg x-show="currentStep > 2" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <span class="ml-3 text-sm font-medium" :class="currentStep >= 2 ? 'text-gray-900' : 'text-gray-500'">
                                    {{ __('storefront.checkout.step_payment') }}
                                </span>
                            </div>

                            <div class="w-16 h-0.5" :class="currentStep > 2 ? 'bg-green-600' : 'bg-gray-200'"></div>

                            <!-- Step 3 -->
                            <div class="flex items-center flex-1">
                                <div
                                    :class="currentStep === 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'"
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-semibold"
                                >
                                    3
                                </div>
                                <span class="ml-3 text-sm font-medium" :class="currentStep >= 3 ? 'text-gray-900' : 'text-gray-500'">
                                    {{ __('storefront.checkout.step_review') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Shipping Information -->
                    <div x-show="currentStep === 1" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.shipping_information') }}</h2>

                        <form @submit.prevent="goToStep(2)" class="space-y-6">
                            <!-- Email -->
                            @guest('customers')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('storefront.checkout.email') }} *
                                </label>
                                <input
                                    type="email"
                                    x-model="formData.email"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="you@example.com"
                                >
                            </div>
                            @endguest

                            <!-- Billing Address -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('storefront.checkout.first_name') }} *
                                    </label>
                                    <input
                                        type="text"
                                        x-model="formData.billing.first_name"
                                        required
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('storefront.checkout.last_name') }} *
                                    </label>
                                    <input
                                        type="text"
                                        x-model="formData.billing.last_name"
                                        required
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('storefront.checkout.company') }}
                                </label>
                                <input
                                    type="text"
                                    x-model="formData.billing.company"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('storefront.checkout.address') }} *
                                </label>
                                <input
                                    type="text"
                                    x-model="formData.billing.address_line_1"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('storefront.checkout.address_line_1') }}"
                                >
                            </div>

                            <div>
                                <input
                                    type="text"
                                    x-model="formData.billing.address_line_2"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('storefront.checkout.address_line_2') }}"
                                >
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('storefront.checkout.city') }} *
                                    </label>
                                    <input
                                        type="text"
                                        x-model="formData.billing.city"
                                        required
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('storefront.checkout.state') }}
                                    </label>
                                    <input
                                        type="text"
                                        x-model="formData.billing.state"
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('storefront.checkout.postal_code') }} *
                                    </label>
                                    <input
                                        type="text"
                                        x-model="formData.billing.postal_code"
                                        required
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('storefront.checkout.country') }} *
                                </label>
                                <select
                                    x-model="formData.billing.country"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="US">United States</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="IT">Italy</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="ES">Spain</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('storefront.checkout.phone') }}
                                </label>
                                <input
                                    type="tel"
                                    x-model="formData.billing.phone"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                            </div>

                            <!-- Shipping Same as Billing -->
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="same-as-billing"
                                    x-model="formData.shipping.same_as_billing"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                >
                                <label for="same-as-billing" class="ml-2 text-sm text-gray-700">
                                    {{ __('storefront.checkout.same_as_billing') }}
                                </label>
                            </div>

                            <!-- Continue Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="!canProceed()"
                                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    {{ __('storefront.checkout.continue_to_payment') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Step 2: Payment & Shipping Method -->
                    <div x-show="currentStep === 2" class="space-y-6">
                        <!-- Shipping Method -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.shipping_method') }}</h2>

                            <div class="space-y-3">
                                <template x-for="method in shippingMethods" :key="method.id">
                                    <label class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-50" :class="formData.shipping_method === method.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                        <div class="flex items-center">
                                            <input
                                                type="radio"
                                                name="shipping_method"
                                                :value="method.id"
                                                x-model="formData.shipping_method"
                                                class="text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900" x-text="method.name"></p>
                                                <p class="text-sm text-gray-500" x-text="method.description"></p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900" x-text="method.price_formatted"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.payment_method') }}</h2>

                            <div class="space-y-3">
                                <template x-for="method in paymentMethods" :key="method.id">
                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" :class="formData.payment_method === method.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            :value="method.id"
                                            x-model="formData.payment_method"
                                            class="text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-900" x-text="method.name"></p>
                                            <p class="text-sm text-gray-500" x-text="method.description"></p>
                                        </div>
                                        <img x-show="method.icon" :src="method.icon" :alt="method.name" class="h-8">
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between">
                            <button
                                @click="goToStep(1)"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                            >
                                {{ __('storefront.checkout.back') }}
                            </button>
                            <button
                                @click="goToStep(3)"
                                :disabled="!canProceed()"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {{ __('storefront.checkout.review_order') }}
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Review & Place Order -->
                    <div x-show="currentStep === 3" class="space-y-6">
                        <!-- Review Shipping -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900">{{ __('storefront.checkout.shipping_address') }}</h2>
                                <button @click="goToStep(1)" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    {{ __('storefront.common.edit') }}
                                </button>
                            </div>
                            <div class="text-sm text-gray-600">
                                <template x-if="formData.shipping.same_as_billing">
                                    <div>
                                        <p x-text="`${formData.billing.first_name} ${formData.billing.last_name}`"></p>
                                        <p x-show="formData.billing.company" x-text="formData.billing.company"></p>
                                        <p x-text="formData.billing.address_line_1"></p>
                                        <p x-show="formData.billing.address_line_2" x-text="formData.billing.address_line_2"></p>
                                        <p x-text="`${formData.billing.city}, ${formData.billing.state} ${formData.billing.postal_code}`"></p>
                                        <p x-text="formData.billing.country"></p>
                                        <p x-show="formData.billing.phone" x-text="formData.billing.phone"></p>
                                    </div>
                                </template>
                                <template x-if="!formData.shipping.same_as_billing">
                                    <div>
                                        <p x-text="`${formData.shipping.first_name} ${formData.shipping.last_name}`"></p>
                                        <p x-show="formData.shipping.company" x-text="formData.shipping.company"></p>
                                        <p x-text="formData.shipping.address_line_1"></p>
                                        <p x-show="formData.shipping.address_line_2" x-text="formData.shipping.address_line_2"></p>
                                        <p x-text="`${formData.shipping.city}, ${formData.shipping.state} ${formData.shipping.postal_code}`"></p>
                                        <p x-text="formData.shipping.country"></p>
                                        <p x-show="formData.shipping.phone" x-text="formData.shipping.phone"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Review Shipping & Payment Method -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900">{{ __('storefront.checkout.shipping_payment') }}</h2>
                                <button @click="goToStep(2)" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    {{ __('storefront.common.edit') }}
                                </button>
                            </div>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-900">{{ __('storefront.checkout.shipping_method') }}:</span>
                                    <span class="ml-2 text-gray-600" x-text="shippingMethods.find(m => m.id === formData.shipping_method)?.name"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900">{{ __('storefront.checkout.payment_method') }}:</span>
                                    <span class="ml-2 text-gray-600" x-text="paymentMethods.find(m => m.id === formData.payment_method)?.name"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('storefront.checkout.order_notes') }}
                            </label>
                            <textarea
                                x-model="formData.notes"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('storefront.checkout.order_notes_placeholder') }}"
                            ></textarea>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between">
                            <button
                                @click="goToStep(2)"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                            >
                                {{ __('storefront.checkout.back') }}
                            </button>
                            <button
                                @click="submitOrder()"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors"
                            >
                                {{ __('storefront.checkout.place_order') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.checkout.order_summary') }}</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-4 pb-4 border-b">
                            @foreach($cart->lines as $line)
                                <div class="flex gap-3">
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
                                        <p class="text-sm text-gray-600">{{ __('storefront.product.quantity') }}: {{ $line->quantity }}</p>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ money($line->total) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary Lines -->
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.subtotal') }}</span>
                                <span class="font-medium text-gray-900">{{ money($cart->subtotal) }}</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.shipping') }}</span>
                                <span class="font-medium text-gray-900" x-text="shippingMethods.find(m => m.id === formData.shipping_method)?.price_formatted || '{{ __('storefront.checkout.calculated_next_step') }}'"></span>
                            </div>

                            @if($cart->discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('storefront.cart.discount') }}</span>
                                    <span class="font-medium text-green-600">-{{ money($cart->discount) }}</span>
                                </div>
                            @endif

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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
