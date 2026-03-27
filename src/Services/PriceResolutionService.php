<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\DTO\PricingContext;
use Cartino\Models\Price;
use Cartino\Models\PriceList;
use Cartino\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PriceResolutionService
{
    /**
     * Resolve price for a variant using PricingContext.
     * Priority: Specific (Market+Site+Channel+Catalog) > Market+Catalog > Site+Catalog > Catalog > Base
     */
    public function resolve(ProductVariant $variant, PricingContext $context): ?Price
    {
        $cacheKey = $context->getCacheKey("price:{$variant->id}");

        return Cache::remember($cacheKey, 300, function () use ($variant, $context) {
            return $this->performResolution($variant, $context);
        });
    }

    /**
     * Perform actual price resolution without cache.
     */
    protected function performResolution(ProductVariant $variant, PricingContext $context): ?Price
    {
        // Build query with base filters
        $query = Price::query()
            ->where('product_variant_id', $variant->id)
            ->forCurrency($context->currency)
            ->forQuantity($context->quantity)
            ->active();

        // Priority 1: Most specific - Market + Site + Channel + PriceList
        $price = (clone $query)
            ->forMarket($context->market?->id)
            ->forSite($context->site?->id)
            ->forChannel($context->channel?->id)
            ->when($context->catalog, fn ($q) => $q->whereHas('priceList', function ($q) use ($context) {
                $q->whereHas('customerGroups', fn ($q2) => $q2->where(
                    'customer_groups.id',
                    $context->customerGroup?->id,
                ));
            }))
            ->orderByDesc('priority')
            ->first();

        if ($price) {
            Log::debug('Price resolved: Most specific (M+S+CH+PL)', [
                'variant_id' => $variant->id,
                'price_id' => $price->id,
                'context' => $context->toArray(),
            ]);

            return $price;
        }

        // Priority 2: Market + Site + Channel (no specific price list)
        $price = (clone $query)
            ->forMarket($context->market?->id)
            ->forSite($context->site?->id)
            ->forChannel($context->channel?->id)
            ->whereNull('price_list_id')
            ->orderByDesc('priority')
            ->first();

        if ($price) {
            Log::debug('Price resolved: Market + Site + Channel', [
                'variant_id' => $variant->id,
                'price_id' => $price->id,
            ]);

            return $price;
        }

        // Priority 3: Market + PriceList (catalog-based)
        if ($context->catalog) {
            $price = (clone $query)
                ->forMarket($context->market?->id)
                ->where('price_list_id', $context->catalog->id)
                ->orderByDesc('priority')
                ->first();

            if ($price) {
                Log::debug('Price resolved: Market + Catalog', [
                    'variant_id' => $variant->id,
                    'price_id' => $price->id,
                ]);

                return $price;
            }
        }

        // Priority 4: Site + PriceList
        if ($context->catalog) {
            $price = (clone $query)
                ->forSite($context->site?->id)
                ->where('price_list_id', $context->catalog->id)
                ->orderByDesc('priority')
                ->first();

            if ($price) {
                Log::debug('Price resolved: Site + Catalog', [
                    'variant_id' => $variant->id,
                    'price_id' => $price->id,
                ]);

                return $price;
            }
        }

        // Priority 5: Market only
        $price = (clone $query)
            ->forMarket($context->market?->id)
            ->whereNull('site_id')
            ->whereNull('channel_id')
            ->whereNull('price_list_id')
            ->orderByDesc('priority')
            ->first();

        if ($price) {
            Log::debug('Price resolved: Market only', [
                'variant_id' => $variant->id,
                'price_id' => $price->id,
            ]);

            return $price;
        }

        // Priority 6: Site only
        $price = (clone $query)
            ->forSite($context->site?->id)
            ->whereNull('market_id')
            ->whereNull('channel_id')
            ->whereNull('price_list_id')
            ->orderByDesc('priority')
            ->first();

        if ($price) {
            Log::debug('Price resolved: Site only', [
                'variant_id' => $variant->id,
                'price_id' => $price->id,
            ]);

            return $price;
        }

        // Priority 7: Base price (all nulls)
        $price = (clone $query)
            ->whereNull('market_id')
            ->whereNull('site_id')
            ->whereNull('channel_id')
            ->whereNull('price_list_id')
            ->orderByDesc('priority')
            ->first();

        if ($price) {
            Log::debug('Price resolved: Base price', [
                'variant_id' => $variant->id,
                'price_id' => $price->id,
            ]);

            return $price;
        }

        Log::warning('No price found for variant', [
            'variant_id' => $variant->id,
            'context' => $context->toArray(),
        ]);

        return null;
    }

    /**
     * Resolve prices for multiple variants in bulk (optimized).
     */
    public function resolveBulk(Collection $variants, PricingContext $context): Collection
    {
        $variantIds = $variants->pluck('id')->toArray();

        // Load all potential prices for these variants in one query
        $allPrices = Price::query()
            ->whereIn('product_variant_id', $variantIds)
            ->forCurrency($context->currency)
            ->forQuantity($context->quantity)
            ->active()
            ->with(['market', 'site', 'channel', 'priceList'])
            ->get()
            ->groupBy('product_variant_id');

        // Resolve each variant's price
        return $variants->mapWithKeys(function ($variant) use ($allPrices, $context) {
            $variantPrices = $allPrices->get($variant->id, collect());
            $resolvedPrice = $this->resolveFromCollection($variantPrices, $context);

            return [$variant->id => $resolvedPrice];
        });
    }

    /**
     * Resolve price from a collection of pre-loaded prices.
     */
    protected function resolveFromCollection(Collection $prices, PricingContext $context): ?Price
    {
        // Apply same priority logic but on collection
        $filtered = $prices->filter(fn ($price) => $this->matchesContext($price, $context));

        return $filtered->sortByDesc(function ($price) use ($context) {
            return $this->calculatePriority($price, $context);
        })->first();
    }

    /**
     * Check if a price matches the context.
     */
    protected function matchesContext(Price $price, PricingContext $context): bool
    {
        // Market match
        if ($price->market_id !== null && $price->market_id !== $context->market?->id) {
            return false;
        }

        // Site match
        if ($price->site_id !== null && $price->site_id !== $context->site?->id) {
            return false;
        }

        // Channel match
        if ($price->channel_id !== null && $price->channel_id !== $context->channel?->id) {
            return false;
        }

        // PriceList/Catalog match
        if ($price->price_list_id !== null && $price->price_list_id !== $context->catalog?->id) {
            return false;
        }

        return true;
    }

    /**
     * Calculate priority score for a price based on context specificity.
     */
    protected function calculatePriority(Price $price, PricingContext $context): int
    {
        $score = 0;

        // More specific = higher score
        if ($price->market_id === $context->market?->id) {
            $score += 1000;
        }
        if ($price->site_id === $context->site?->id) {
            $score += 100;
        }
        if ($price->channel_id === $context->channel?->id) {
            $score += 10;
        }
        if ($price->price_list_id === $context->catalog?->id) {
            $score += 1;
        }

        return $score;
    }

    /**
     * Get all price tiers for a variant (quantity breaks).
     */
    public function getTiers(ProductVariant $variant, PricingContext $context): Collection
    {
        $cacheKey = $context->getCacheKey("price_tiers:{$variant->id}");

        return Cache::remember($cacheKey, 300, function () use ($variant, $context) {
            return Price::query()
                ->where('product_variant_id', $variant->id)
                ->forMarket($context->market?->id)
                ->forSite($context->site?->id)
                ->forChannel($context->channel?->id)
                ->forCurrency($context->currency)
                ->active()
                ->orderBy('min_quantity')
                ->get();
        });
    }

    /**
     * Get available price lists for a customer group in a specific context.
     */
    public function getAvailablePriceLists(PricingContext $context): Collection
    {
        if (! $context->customerGroup) {
            return collect();
        }

        return PriceList::query()
            ->whereHas('customerGroups', function ($q) use ($context) {
                $q->where('customer_groups.id', $context->customerGroup->id);
            })
            ->active()
            ->orderByDesc('priority')
            ->get();
    }

    /**
     * Clear cache for a specific variant.
     */
    public function clearCache(ProductVariant $variant): void
    {
        Cache::tags(['prices', "variant:{$variant->id}"])->flush();
    }

    /**
     * Clear all pricing cache.
     */
    public function clearAllCache(): void
    {
        Cache::tags(['prices'])->flush();
    }

    /**
     * Get price with applied catalog adjustments if configured.
     */
    public function resolveWithAdjustments(ProductVariant $variant, PricingContext $context): ?Price
    {
        $price = $this->resolve($variant, $context);

        if (! $price || ! $context->catalog) {
            return $price;
        }

        // Apply catalog adjustments if configured
        if ($context->catalog->adjustment_type && $context->catalog->adjustment_value) {
            $adjustedAmount = $this->applyCatalogAdjustment(
                $price->amount,
                $context->catalog->adjustment_type,
                $context->catalog->adjustment_direction,
                $context->catalog->adjustment_value,
            );

            // Clone price with adjusted amount (don't modify original)
            $price = $price->replicate();
            $price->amount = $adjustedAmount;
        }

        return $price;
    }

    /**
     * Apply catalog adjustment to a price amount.
     */
    protected function applyCatalogAdjustment(int $amount, string $type, string $direction, float $value): int
    {
        if ($type === 'percentage') {
            $adjustment = (int) round($amount * ($value / 100));
        } else {
            $adjustment = (int) ($value * 100); // Convert to cents
        }

        return $direction === 'increase' ? ($amount + $adjustment) : ($amount - $adjustment);
    }
}
