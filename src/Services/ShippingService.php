<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\ShippingRate;
use Cartino\Models\ShippingZone;
use Illuminate\Support\Collection;

class ShippingService
{
    /**
     * Get available shipping rates for destination and cart details.
     */
    public function getAvailableRates(
        string $countryCode,
        ?string $region = null,
        ?string $postalCode = null,
        float $orderValue = 0,
        float $totalWeight = 0,
        array $productIds = [],
        ?int $marketId = null,
    ): Collection {
        // Find matching zones by priority
        $zones = ShippingZone::active()
            ->orderByDesc('priority')
            ->get()
            ->filter(fn ($zone) => $zone->coversAddress($countryCode, $region, $postalCode));

        if ($zones->isEmpty()) {
            return collect();
        }

        // Get rates from matching zones
        $rates = collect();
        foreach ($zones as $zone) {
            $zoneRates = ShippingRate::where('shipping_zone_id', $zone->id)
                ->active()
                ->when($marketId, fn ($q) => $q->where(
                    fn ($q2) => $q2->where('market_id', $marketId)->orWhereNull('market_id'),
                ))
                ->orderByDesc('priority')
                ->get();

            foreach ($zoneRates as $rate) {
                $price = $rate->calculatePrice($orderValue, $totalWeight, $productIds);

                if ($price !== null) {
                    $rates->push([
                        'id' => $rate->id,
                        'name' => $rate->name,
                        'description' => $rate->description,
                        'price' => $price,
                        'currency' => $rate->currency,
                        'delivery_estimate' => $this->formatDeliveryEstimate($rate),
                        'carrier' => $rate->carrier,
                        'zone_name' => $zone->name,
                    ]);
                }
            }
        }

        return $rates->sortBy('price')->values();
    }

    /**
     * Calculate total shipping for cart items.
     */
    public function calculateShipping(
        int $shippingRateId,
        float $orderValue,
        float $totalWeight,
        int $itemCount = 1,
        array $productIds = [],
    ): array {
        $rate = ShippingRate::findOrFail($shippingRateId);

        $price = match ($rate->calculation_method) {
            'flat_rate' => $rate->price,
            'per_item' => $rate->price * $itemCount,
            'weight_based' => $rate->calculatePrice($orderValue, $totalWeight, $productIds),
            'price_based' => $rate->calculatePrice($orderValue, $totalWeight, $productIds),
            default => $rate->price,
        };

        return [
            'rate_id' => $rate->id,
            'name' => $rate->name,
            'price' => $price,
            'currency' => $rate->currency,
            'delivery_estimate' => $this->formatDeliveryEstimate($rate),
        ];
    }

    protected function formatDeliveryEstimate(ShippingRate $rate): ?string
    {
        if (! $rate->min_delivery_days && ! $rate->max_delivery_days) {
            return null;
        }

        if ($rate->min_delivery_days === $rate->max_delivery_days) {
            return "{$rate->min_delivery_days} days";
        }

        return "{$rate->min_delivery_days}-{$rate->max_delivery_days} days";
    }
}
