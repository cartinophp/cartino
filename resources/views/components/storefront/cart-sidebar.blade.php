@props(['cart' => null])

<div
    x-data="{
        open: false,
        cart: @js($cart ?? []),
        loading: false,

        async updateQuantity(lineId, quantity) {
            this.loading = true;
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch(`/cart/update/${lineId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ quantity }),
                    signal: AbortSignal.timeout(10000)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.cart = data.cart;
                $dispatch('cart-updated', data.cart);
            } catch (error) {
                console.error('Error updating cart:', error);
                $dispatch('show-notification', {
                    detail: { type: 'error', message: '{{ __('storefront.cart.update_error') }}' }
                });
            } finally {
                this.loading = false;
            }
        },

        async removeItem(lineId) {
            if (!confirm('{{ __('storefront.cart.confirm_remove') }}')) return;

            this.loading = true;
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch(`/cart/remove/${lineId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    signal: AbortSignal.timeout(10000)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.cart = data.cart;
                $dispatch('cart-updated', data.cart);
            } catch (error) {
                console.error('Error removing item:', error);
                $dispatch('show-notification', {
                    detail: { type: 'error', message: '{{ __('storefront.cart.remove_error') }}' }
                });
            } finally {
                this.loading = false;
            }
        },

        get itemCount() {
            return this.cart.lines?.reduce((sum, line) => sum + line.quantity, 0) || 0;
        },

        get subtotal() {
            return this.cart.subtotal || 0;
        },

        get total() {
            return this.cart.total || 0;
        }
    }"
    @cart-open.window="open = true"
    @cart-close.window="open = false"
    @cart-updated.window="cart = $event.detail"
    @keydown.escape.window="open = false"
>
    <!-- Overlay -->
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"
        style="display: none;"
    ></div>

    <!-- Sidebar -->
    <div
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 bottom-0 w-full sm:w-96 bg-white shadow-xl z-50 flex flex-col"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ __('storefront.cart.title') }}
                <span x-show="itemCount > 0" x-text="`(${itemCount})`" class="text-gray-500"></span>
            </h2>
            <button
                @click="open = false"
                class="text-gray-400 hover:text-gray-500"
                aria-label="Close cart"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <!-- Loading State -->
            <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Empty Cart -->
            <div x-show="!cart.lines || cart.lines.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <p class="mt-4 text-gray-500">{{ __('storefront.cart.empty') }}</p>
                <button
                    @click="open = false"
                    class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                >
                    {{ __('storefront.cart.continue_shopping') }}
                </button>
            </div>

            <!-- Cart Items List -->
            <div x-show="cart.lines && cart.lines.length > 0" class="space-y-4">
                <template x-for="line in cart.lines" :key="line.id">
                    <div class="flex gap-4 p-4 bg-gray-50 rounded-lg">
                        <!-- Product Image -->
                        <div class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-md overflow-hidden">
                            <img
                                :src="line.product.image || '/placeholder.png'"
                                :alt="line.product.name"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-900 truncate" x-text="line.product.name"></h3>

                            <!-- Variant -->
                            <p x-show="line.variant" class="mt-1 text-xs text-gray-500" x-text="line.variant?.name"></p>

                            <!-- Price -->
                            <p class="mt-1 text-sm font-medium text-gray-900" x-text="line.price_formatted"></p>

                            <!-- Quantity Controls -->
                            <div class="mt-2 flex items-center gap-2">
                                <button
                                    @click="updateQuantity(line.id, line.quantity - 1)"
                                    :disabled="line.quantity <= 1"
                                    class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>

                                <span class="text-sm font-medium w-8 text-center" x-text="line.quantity"></span>

                                <button
                                    @click="updateQuantity(line.id, line.quantity + 1)"
                                    class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>

                                <button
                                    @click="removeItem(line.id)"
                                    class="ml-auto text-red-600 hover:text-red-700"
                                    :title="'{{ __('storefront.cart.remove') }}'"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <div x-show="cart.lines && cart.lines.length > 0" class="border-t px-6 py-4 space-y-4">
            <!-- Subtotal -->
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ __('storefront.cart.subtotal') }}</span>
                <span class="font-medium text-gray-900" x-text="cart.subtotal_formatted"></span>
            </div>

            <!-- Shipping Notice -->
            <div class="flex items-start gap-2 text-sm text-gray-600">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p>{{ __('storefront.cart.shipping_calculated_checkout') }}</p>
            </div>

            <!-- Checkout Button -->
            <button
                @click="window.location.href = '{{ route('storefront.checkout') }}'"
                class="w-full bg-indigo-600 text-white rounded-lg px-6 py-3 font-medium hover:bg-indigo-700 transition-colors"
            >
                {{ __('storefront.cart.checkout') }}
            </button>

            <!-- Continue Shopping -->
            <button
                @click="open = false"
                class="w-full text-center text-sm text-gray-600 hover:text-gray-900"
            >
                {{ __('storefront.cart.continue_shopping') }}
            </button>
        </div>
async addItem(productId, variantId = null, quantity = 1) {
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity }),
            signal: AbortSignal.timeout(10000)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
            this.open();

            // Show notification
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type: 'success', message: data.message }
            }));
        } else {
            throw new Error(data.message || 'Failed to add item to cart');
        }

        return data;
    } catch (error) {
        console.error('Error adding to cart:', error);
        window.dispatchEvent(new CustomEvent('show-notification', {
            detail: { type: 'error', message: '{{ __('storefront.cart.add_error') }}' }
        }));
        throw error;
    }
}
                    window.dispatchEvent(new CustomEvent('show-notification', {
                        detail: { type: 'success', message: data.message }
                    }));
                }

                return data;
            } catch (error) {
                console.error('Error adding to cart:', error);
                window.dispatchEvent(new CustomEvent('show-notification', {
                    detail: { type: 'error', message: 'Error adding item to cart' }
                }));
            }
        }
    };
</script>
@endpush
