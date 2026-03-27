<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\DTO\PricingContext;
use Cartino\Models\Catalog;
use Cartino\Models\InventoryLocation;
use Cartino\Models\Market;
use Cartino\Models\PaymentMethod;
use Cartino\Models\ShippingMethod;
use Cartino\Models\TaxRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MarketConfigurationService
{
    /**
     * Get available payment methods for a market/context.
     */
    public function getAvailablePaymentMethods(
        ?Market $market = null,
        ?PricingContext $context = null,
    ): Collection {
        $market = $market ?? $context?->market;

        if (! $market) {
            return PaymentMethod::query()->where('is_active', true)->get();
        }

        $cacheKey = "market:{$market->id}:payment_methods";

        return Cache::remember($cacheKey, 3600, function () use ($market) {
            // If market has specific payment methods configured, use those
            if (! empty($market->payment_methods)) {
                return PaymentMethod::query()
                    ->whereIn('code', $market->payment_methods)
                    ->where('is_active', true)
                    ->orderBy('priority')
                    ->get();
            }

            // Otherwise return all active payment methods
            return PaymentMethod::query()
                ->where('is_active', true)
                ->orderBy('priority')
                ->get();
        });
    }

    /**
     * Check if a payment method is available in a market.
     */
    public function isPaymentMethodAvailable(string $paymentMethodCode, Market $market): bool
    {
        if (empty($market->payment_methods)) {
            return true; // No restrictions
        }

        return in_array($paymentMethodCode, $market->payment_methods);
    }

    /**
     * Get available shipping methods for a market/context.
     */
    public function getAvailableShippingMethods(
        ?Market $market = null,
        ?PricingContext $context = null,
        ?string $countryCode = null,
    ): Collection {
        $market = $market ?? $context?->market;
        $countryCode = $countryCode ?? $context?->countryCode;

        if (! $market) {
            return ShippingMethod::query()->where('is_active', true)->get();
        }

        $cacheKey = "market:{$market->id}:shipping_methods:{$countryCode}";

        return Cache::remember($cacheKey, 3600, function () use ($market, $countryCode) {
            $query = ShippingMethod::query()->where('is_active', true);

            // If market has specific shipping methods configured
            if (! empty($market->shipping_methods)) {
                $query->whereIn('code', $market->shipping_methods);
            }

            // Filter by country if provided
            if ($countryCode) {
                $query->whereHas('shippingZones', function ($q) use ($countryCode) {
                    $q->whereJsonContains('countries', $countryCode)->orWhereNull('countries'); // Global zones
                });
            }

            return $query->orderBy('priority')->get();
        });
    }

    /**
     * Check if a shipping method is available in a market.
     */
    public function isShippingMethodAvailable(string $shippingMethodCode, Market $market): bool
    {
        if (empty($market->shipping_methods)) {
            return true; // No restrictions
        }

        return in_array($shippingMethodCode, $market->shipping_methods);
    }

    /**
     * Get applicable tax rates for a market/context.
     */
    public function getApplicableTaxRates(
        ?Market $market = null,
        ?PricingContext $context = null,
        ?string $countryCode = null,
        ?string $stateCode = null,
    ): Collection {
        $market = $market ?? $context?->market;
        $countryCode = $countryCode ?? $context?->countryCode;
        $taxRegion = $market?->tax_region ?? $countryCode;

        if (! $taxRegion) {
            return collect();
        }

        $cacheKey = "tax_rates:{$taxRegion}:{$stateCode}";

        return Cache::remember($cacheKey, 3600, function () use ($taxRegion, $stateCode) {
            $query = TaxRate::query()->active();

            // Filter by country
            $query->where(function ($q) use ($taxRegion) {
                $q->whereJsonContains('countries', $taxRegion)->orWhereNull('countries'); // Global rates
            });

            // Filter by state if provided
            if ($stateCode) {
                $query->where(function ($q) use ($stateCode) {
                    $q->whereJsonContains('states', $stateCode)->orWhereNull('states');
                });
            }

            return $query->orderBy('priority', 'desc')->get();
        });
    }

    /**
     * Calculate tax for an amount in a specific market.
     */
    public function calculateTax(
        int $amount,
        Market $market,
        ?string $countryCode = null,
        ?string $productType = null,
    ): array {
        $taxRates = $this->getApplicableTaxRates($market, null, $countryCode);

        // Filter by product type if provided
        if ($productType) {
            $taxRates = $taxRates->filter(function ($rate) use ($productType) {
                return empty($rate->product_collections) || in_array($productType, $rate->product_collections);
            });
        }

        $taxAmount = 0;
        $effectiveTaxRate = 0;

        foreach ($taxRates as $rate) {
            if ($rate->type === 'percentage') {
                $rateAmount = (int) round($amount * ($rate->rate / 100));

                // Handle compound taxes (tax on tax)
                if ($rate->is_compound) {
                    $rateAmount = (int) round(($amount + $taxAmount) * ($rate->rate / 100));
                }

                $taxAmount += $rateAmount;
                $effectiveTaxRate += $rate->rate;
            } else {
                // Fixed tax amount
                $taxAmount += (int) ($rate->rate * 100);
            }
        }

        return [
            'tax_amount' => $taxAmount,
            'amount_without_tax' => $amount,
            'amount_with_tax' => $amount + $taxAmount,
            'effective_tax_rate' => round($effectiveTaxRate, 4),
            'applied_rates' => $taxRates
                ->map(fn ($rate) => [
                    'name' => $rate->name,
                    'rate' => $rate->rate,
                    'type' => $rate->type,
                ])
                ->toArray(),
        ];
    }

    /**
     * Get fulfillment locations for a market.
     */
    public function getFulfillmentLocations(Market $market): array
    {
        if (empty($market->fulfillment_locations)) {
            // Return all warehouses/stores
            return InventoryLocation::query()
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        return $market->fulfillment_locations;
    }

    /**
     * Get market-specific settings.
     */
    public function getSettings(Market $market, ?string $key = null): mixed
    {
        if (! $market->settings) {
            return $key ? null : [];
        }

        if ($key) {
            return data_get($market->settings, $key);
        }

        return $market->settings;
    }

    /**
     * Update market-specific settings.
     */
    public function updateSettings(Market $market, string $key, mixed $value): bool
    {
        $settings = $market->settings ?? [];
        data_set($settings, $key, $value);

        return $market->update(['settings' => $settings]);
    }

    /**
     * Get market configuration summary.
     */
    public function getConfigurationSummary(Market $market): array
    {
        return [
            'market' => [
                'id' => $market->id,
                'code' => $market->code,
                'name' => $market->name,
                'type' => $market->type,
            ],
            'currencies' => [
                'default' => $market->default_currency,
                'supported' => $market->getCurrencies(),
            ],
            'locales' => [
                'default' => $market->default_locale,
                'supported' => $market->getLocales(),
            ],
            'tax' => [
                'included_in_prices' => $market->tax_included_in_prices,
                'region' => $market->tax_region,
                'rates_count' => $this->getApplicableTaxRates($market)->count(),
            ],
            'payment_methods' => [
                'count' => $this->getAvailablePaymentMethods($market)->count(),
                'codes' => $market->payment_methods ?? [],
            ],
            'shipping_methods' => [
                'count' => $this->getAvailableShippingMethods($market)->count(),
                'codes' => $market->shipping_methods ?? [],
            ],
            'fulfillment' => [
                'locations_count' => count($this->getFulfillmentLocations($market)),
            ],
            'catalog' => [
                'id' => $market->catalog_id,
                'use_catalog_prices' => $market->use_catalog_prices,
            ],
        ];
    }

    /**
     * Clear cache for a market.
     */
    public function clearCache(Market $market): void
    {
        Cache::tags(["market:{$market->id}"])->flush();
    }

    /**
     * Validate market configuration.
     */
    public function validate(Market $market): array
    {
        $errors = [];

        // Check required fields
        if (empty($market->default_currency)) {
            $errors[] = 'Default currency is required';
        }

        if (empty($market->default_locale)) {
            $errors[] = 'Default locale is required';
        }

        // Check payment methods exist
        if (! empty($market->payment_methods)) {
            $existingMethods = PaymentMethod::whereIn('code', $market->payment_methods)->pluck('code');
            $missing = array_diff($market->payment_methods, $existingMethods->toArray());

            if (! empty($missing)) {
                $errors[] = 'Payment methods not found: '.implode(', ', $missing);
            }
        }

        // Check shipping methods exist
        if (! empty($market->shipping_methods)) {
            $existingMethods = ShippingMethod::whereIn('code', $market->shipping_methods)->pluck('code');
            $missing = array_diff($market->shipping_methods, $existingMethods->toArray());

            if (! empty($missing)) {
                $errors[] = 'Shipping methods not found: '.implode(', ', $missing);
            }
        }

        // Check catalog exists
        if ($market->catalog_id && ! Catalog::find($market->catalog_id)) {
            $errors[] = 'Catalog not found';
        }

        return $errors;
    }
}
