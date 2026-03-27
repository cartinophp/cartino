<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Market;
use Cartino\Models\ProductVariant;
use Cartino\Models\VariantPrice;

class PricingService
{
    /**
     * Resolve price for a variant with hierarchical fallback.
     * Priority: Specific Price > Catalog Price > Base Price.
     */
    public function resolvePrice(
        ProductVariant $variant,
        ?int $marketId = null,
        ?int $customerGroupId = null,
        ?string $currency = null,
        int $quantity = 1,
    ): array {
        $context = [
            'market_id' => $marketId,
            'customer_group_id' => $customerGroupId,
            'currency' => $currency ?? config('cartino.currency'),
            'quantity' => $quantity,
        ];

        // Try to find specific price
        $specificPrice = VariantPrice::where('product_variant_id', $variant->id)
            ->forContext($context)
            ->active()
            ->byPriority()
            ->first();

        if ($specificPrice) {
            return [
                'price' => $specificPrice->price,
                'compare_at_price' => $specificPrice->compare_at_price,
                'currency' => $specificPrice->currency,
                'source' => 'specific',
                'price_id' => $specificPrice->id,
            ];
        }

        // Fallback to market catalog price
        if ($marketId) {
            $market = Market::find($marketId);
            if ($market && $market->use_catalog_prices && $market->catalog_id) {
                $catalogPrice = $variant->catalogPrices()->where('catalog_id', $market->catalog_id)->first();

                if ($catalogPrice && $catalogPrice->fixed_price) {
                    return [
                        'price' => $catalogPrice->fixed_price,
                        'compare_at_price' => $catalogPrice->compare_at_price,
                        'currency' => $market->currency,
                        'source' => 'catalog',
                        'catalog_id' => $market->catalog_id,
                    ];
                }
            }
        }

        // Fallback to base variant price
        return [
            'price' => $variant->price,
            'compare_at_price' => $variant->compare_at_price,
            'currency' => $currency ?? config('cartino.currency'),
            'source' => 'base',
        ];
    }

    /**
     * Get all available price tiers for quantity breaks.
     */
    public function getPriceTiers(
        ProductVariant $variant,
        ?int $marketId = null,
        ?int $customerGroupId = null,
        ?string $currency = null,
    ): array {
        $context = array_filter([
            'market_id' => $marketId,
            'customer_group_id' => $customerGroupId,
            'currency' => $currency,
        ]);

        return VariantPrice::where('product_variant_id', $variant->id)
            ->forContext($context)
            ->active()
            ->orderBy('min_quantity')
            ->get()
            ->map(fn ($price) => [
                'min_quantity' => $price->min_quantity,
                'max_quantity' => $price->max_quantity,
                'price' => $price->price,
                'currency' => $price->currency,
            ])
            ->toArray();
    }

    /**
     * Bulk resolve prices for multiple variants (optimized).
     */
    public function resolvePricesBulk(
        array $variantIds,
        ?int $marketId = null,
        ?int $customerGroupId = null,
        ?string $currency = null,
        int $quantity = 1,
    ): array {
        $context = [
            'market_id' => $marketId,
            'customer_group_id' => $customerGroupId,
            'currency' => $currency ?? config('cartino.currency'),
            'quantity' => $quantity,
        ];

        // Load all specific prices in one query
        $specificPrices = VariantPrice::whereIn('product_variant_id', $variantIds)
            ->forContext($context)
            ->active()
            ->byPriority()
            ->get()
            ->groupBy('product_variant_id')
            ->map->first();

        // Load all variants with base prices
        $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        $results = [];
        foreach ($variantIds as $variantId) {
            $variant = $variants[$variantId] ?? null;
            if (! $variant) {
                continue;
            }

            if (isset($specificPrices[$variantId])) {
                $price = $specificPrices[$variantId];
                $results[$variantId] = [
                    'price' => $price->price,
                    'compare_at_price' => $price->compare_at_price,
                    'currency' => $price->currency,
                    'source' => 'specific',
                ];
            } else {
                $results[$variantId] = [
                    'price' => $variant->price,
                    'compare_at_price' => $variant->compare_at_price,
                    'currency' => $currency ?? config('cartino.currency'),
                    'source' => 'base',
                ];
            }
        }

        return $results;
    }
}
