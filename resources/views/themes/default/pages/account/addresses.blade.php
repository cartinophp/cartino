@extends('themes.default.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.account.addresses') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.account.addresses_description') }}</p>
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
                            class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            {{ __('storefront.account.orders') }}
                        </a>

                        <a
                            href="{{ route('storefront.account.addresses') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium bg-indigo-50 text-indigo-700 rounded-lg"
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

            <!-- Addresses List -->
            <div class="lg:col-span-2">
                <div
                    x-data="{
                        showAddressForm: false,
                        editingAddress: null,
                        formData: {
                            first_name: '',
                            last_name: '',
                            company: '',
                            address_line_1: '',
                            address_line_2: '',
                            city: '',
                            state: '',
                            postal_code: '',
                            country: 'US',
                            phone: '',
                            is_default: false,
                            type: 'both'
                        },

                        openAddressForm(address = null) {
                            if (address) {
                                this.editingAddress = address;
                                this.formData = { ...address };
                            } else {
                                this.editingAddress = null;
                                this.formData = {
                                    first_name: @js($customer->first_name),
                                    last_name: @js($customer->last_name),
                                    company: '',
                                    address_line_1: '',
                                    address_line_2: '',
                                    city: '',
                                    state: '',
                                    postal_code: '',
                                    country: 'US',
                                    phone: @js($customer->phone ?? ''),
                                    is_default: false,
                                    type: 'both'
                                };
                            }
                            this.showAddressForm = true;
                        },

                        async saveAddress() {
                            const url = this.editingAddress
                                ? `{{ route('storefront.account.addresses.index') }}/${this.editingAddress.id}`
                                : '{{ route('storefront.account.addresses.store') }}';

                            const method = this.editingAddress ? 'PUT' : 'POST';

                            try {
                                const response = await fetch(url, {
                                    method: method,
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    },
                                    body: JSON.stringify(this.formData)
                                });

                                const data = await response.json();

                                if (data.success) {
                                    window.location.reload();
                                } else {
                                    alert(data.message || '{{ __('storefront.messages.error') }}');
                                }
                            } catch (error) {
                                console.error('Error saving address:', error);
                                alert('{{ __('storefront.messages.error') }}');
                            }
                        },

                        async deleteAddress(addressId) {
                            if (!confirm('{{ __('storefront.account.confirm_delete_address') }}')) return;

                            try {
                                const response = await fetch(`{{ route('storefront.account.addresses.index') }}/${addressId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    }
                                });

                                const data = await response.json();

                                if (data.success) {
                                    window.location.reload();
                                } else {
                                    alert(data.message || '{{ __('storefront.messages.error') }}');
                                }
                            } catch (error) {
                                console.error('Error deleting address:', error);
                                alert('{{ __('storefront.messages.error') }}');
                            }
                        }
                    }"
                >
                    <!-- Add New Address Button -->
                    <div class="mb-6">
                        <button
                            @click="openAddressForm()"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('storefront.account.add_new_address') }}
                        </button>
                    </div>

                    <!-- Addresses Grid -->
                    @if($addresses->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($addresses as $address)
                                <div class="bg-white rounded-lg shadow-sm p-6 relative">
                                    <!-- Default Badge -->
                                    @if($address->is_default)
                                        <span class="absolute top-4 right-4 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            {{ __('storefront.account.default') }}
                                        </span>
                                    @endif

                                    <!-- Address Type -->
                                    <div class="mb-3">
                                        <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                            @if($address->type === 'billing')
                                                {{ __('storefront.checkout.billing_address') }}
                                            @elseif($address->type === 'shipping')
                                                {{ __('storefront.checkout.shipping_address') }}
                                            @else
                                                {{ __('storefront.account.billing_and_shipping') }}
                                            @endif
                                        </span>
                                    </div>

                                    <!-- Address Details -->
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p class="font-medium text-gray-900">{{ $address->full_name }}</p>
                                        @if($address->company)
                                            <p>{{ $address->company }}</p>
                                        @endif
                                        <p>{{ $address->address_line_1 }}</p>
                                        @if($address->address_line_2)
                                            <p>{{ $address->address_line_2 }}</p>
                                        @endif
                                        <p>{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                        <p>{{ $address->country_name }}</p>
                                        @if($address->phone)
                                            <p>{{ $address->phone }}</p>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-4 pt-4 border-t flex items-center gap-3">
                                        <button
                                            @click="openAddressForm(@js($address))"
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                        >
                                            {{ __('storefront.common.edit') }}
                                        </button>
                                        @if(!$address->is_default)
                                            <button
                                                @click="deleteAddress({{ $address->id }})"
                                                class="text-sm font-medium text-red-600 hover:text-red-700"
                                            >
                                                {{ __('storefront.common.delete') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ __('storefront.account.no_addresses') }}</h2>
                            <p class="mt-2 text-gray-500">{{ __('storefront.account.no_addresses_description') }}</p>
                        </div>
                    @endif

                    <!-- Address Form Modal -->
                    <div
                        x-show="showAddressForm"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showAddressForm = false"
                        class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4"
                        style="display: none;"
                    >
                        <div
                            @click.stop
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-4"
                            class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                        >
                            <div class="px-6 py-4 border-b flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900" x-text="editingAddress ? '{{ __('storefront.account.edit_address') }}' : '{{ __('storefront.account.add_new_address') }}'"></h2>
                                <button @click="showAddressForm = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form @submit.prevent="saveAddress()" class="px-6 py-4 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.first_name') }} *</label>
                                        <input type="text" x-model="formData.first_name" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.last_name') }} *</label>
                                        <input type="text" x-model="formData.last_name" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.company') }}</label>
                                    <input type="text" x-model="formData.company" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.address') }} *</label>
                                    <input type="text" x-model="formData.address_line_1" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('storefront.checkout.address_line_1') }}">
                                </div>

                                <div>
                                    <input type="text" x-model="formData.address_line_2" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('storefront.checkout.address_line_2') }}">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.city') }} *</label>
                                        <input type="text" x-model="formData.city" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.state') }}</label>
                                        <input type="text" x-model="formData.state" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.postal_code') }} *</label>
                                        <input type="text" x-model="formData.postal_code" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.country') }} *</label>
                                    <select x-model="formData.country" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="IT">Italy</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="ES">Spain</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.phone') }}</label>
                                    <input type="tel" x-model="formData.phone" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.address_type') }}</label>
                                    <select x-model="formData.type" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="both">{{ __('storefront.account.billing_and_shipping') }}</option>
                                        <option value="billing">{{ __('storefront.checkout.billing_address') }}</option>
                                        <option value="shipping">{{ __('storefront.checkout.shipping_address') }}</option>
                                    </select>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" id="is_default" x-model="formData.is_default" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_default" class="ml-2 text-sm text-gray-700">{{ __('storefront.account.set_as_default') }}</label>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t">
                                    <button type="button" @click="showAddressForm = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                        {{ __('storefront.common.cancel') }}
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                        {{ __('storefront.common.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
