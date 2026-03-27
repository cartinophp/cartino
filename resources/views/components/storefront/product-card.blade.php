@props([
    'product',
    'lazy' => true,
])

<div class="group relative">
    <a href="{{ route('storefront.products.show', $product->handle) }}" class="block">
        <!-- Product Image -->
        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
            @if($product->getFirstMediaUrl('images'))
                <img
                    src="{{ $product->getFirstMediaUrl('images', 'medium') }}"
                    alt="{{ $product->name }}"
                    @if($lazy) loading="lazy" @endif
                    class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                >
            @else
                <div class="flex h-full w-full items-center justify-center bg-gray-200">
                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif

            <!-- Badges -->
            <div class="absolute top-2 left-2 flex flex-col gap-2">
                @if($product->is_featured)
                    <span class="inline-flex items-center rounded-full bg-indigo-600 px-2.5 py-0.5 text-xs font-medium text-white">
                        Featured
                    </span>
                @endif

                @if($product->compare_price && $product->compare_price > $product->price)
                    @php
                        $discount = round((($product->compare_price - $product->price) / $product->compare_price) * 100);
                    @endphp
                    <span class="inline-flex items-center rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-medium text-white">
                        -{{ $discount }}%
                    </span>
                @endif

                @if($product->stock_quantity <= 0 && $product->track_quantity)
                    <span class="inline-flex items-center rounded-full bg-gray-600 px-2.5 py-0.5 text-xs font-medium text-white">
                        {{ __('storefront.product.out_of_stock') }}
                    </span>
                @elseif($product->stock_quantity <= 10 && $product->track_quantity)
                    <span class="inline-flex items-center rounded-full bg-orange-600 px-2.5 py-0.5 text-xs font-medium text-white">
                        {{ __('storefront.product.low_stock', ['count' => $product->stock_quantity]) }}
                    </span>
                @endif
            </div>

            <!-- Quick Actions (Hover) -->
            <div class="absolute bottom-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <!-- Quick View -->
                <button
                    @click.prevent="$dispatch('quick-view', { productId: {{ $product->id }} })"
                    class="rounded-full bg-white p-2 shadow-md hover:bg-gray-100"
                    title="Quick View"
                >
                    <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>

                <!-- Wishlist -->
                <button
                    @click.prevent="$dispatch('toggle-wishlist', { productId: {{ $product->id }} })"
                    class="rounded-full bg-white p-2 shadow-md hover:bg-gray-100"
                    title="Add to Wishlist"
                >
                    <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Product Info -->
        <div class="mt-3 space-y-1">
            <!-- Brand -->
            @if($product->brand)
                <p class="text-xs text-gray-500">{{ $product->brand->name }}</p>
            @endif

            <!-- Name -->
            <h3 class="text-sm font-medium text-gray-900 line-clamp-2 group-hover:text-indigo-600">
                {{ $product->name }}
            </h3>

            <!-- Rating -->
            @if($product->average_rating > 0)
                <div class="flex items-center gap-1">
                    <div class="flex">
                        @for($i = 1; $i <= 5; $i++)
                            <svg
                                class="h-4 w-4 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    @if($product->review_count > 0)
                        <span class="text-xs text-gray-500">({{ $product->review_count }})</span>
                    @endif
                </div>
            @endif

            <!-- Price -->
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-gray-900">
                    {{ money($product->price) }}
                </span>

                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-sm text-gray-500 line-through">
                        {{ money($product->compare_price) }}
                    </span>
                @endif
            </div>
        </div>
    </a>

    <!-- Quick Add to Cart -->
    @if($product->stock_quantity > 0 || !$product->track_quantity)
        <button
            @click="$dispatch('add-to-cart', { productId: {{ $product->id }}, quantity: 1 })"
            class="mt-2 w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white opacity-0 hover:bg-indigo-700 group-hover:opacity-100 transition-opacity focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            {{ __('storefront.product.add_to_cart') }}
        </button>
    @else
        <button
            disabled
            class="mt-2 w-full rounded-md bg-gray-300 px-4 py-2 text-sm font-medium text-gray-500 cursor-not-allowed"
        >
            {{ __('storefront.product.out_of_stock') }}
        </button>
    @endif
</div>
