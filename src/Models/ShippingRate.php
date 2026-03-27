<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shipping_zone_id',
        'market_id',
        'name',
        'code',
        'description',
        'calculation_method',
        'price',
        'currency',
        'min_price',
        'max_price',
        'min_weight',
        'max_weight',
        'weight_unit',
        'min_order_value',
        'max_order_value',
        'min_delivery_days',
        'max_delivery_days',
        'carrier',
        'service_code',
        'carrier_settings',
        'is_active',
        'priority',
        'data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_order_value' => 'decimal:2',
        'min_delivery_days' => 'integer',
        'max_delivery_days' => 'integer',
        'carrier_settings' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'data' => 'array',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(ShippingRateTier::class);
    }

    public function excludedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shipping_rate_product_exclusions');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculatePrice(float $orderValue, float $weight, array $productIds = []): ?float
    {
        // Check exclusions
        if (! empty($productIds)) {
            $hasExcludedProduct = $this->excludedProducts()->whereIn('product_id', $productIds)->exists();

            if ($hasExcludedProduct) {
                return null;
            }
        }

        return match ($this->calculation_method) {
            'flat_rate' => $this->price,
            'per_item' => null, // Handled separately
            'weight_based' => $this->calculateWeightBased($weight),
            'price_based' => $this->calculatePriceBased($orderValue),
            'carrier_calculated' => null, // Requires external API
            default => $this->price,
        };
    }

    protected function calculateWeightBased(float $weight): ?float
    {
        if ($this->min_weight !== null && $weight < $this->min_weight) {
            return null;
        }

        if ($this->max_weight !== null && $weight > $this->max_weight) {
            return null;
        }

        $tier = $this->tiers()
            ->where('min_value', '<=', $weight)
            ->where(fn ($q) => $q->whereNull('max_value')->orWhere('max_value', '>=', $weight))
            ->first();

        return $tier?->price ?? $this->price;
    }

    protected function calculatePriceBased(float $orderValue): ?float
    {
        if ($this->min_price !== null && $orderValue < $this->min_price) {
            return null;
        }

        if ($this->max_price !== null && $orderValue > $this->max_price) {
            return null;
        }

        // Free shipping threshold
        if ($this->min_order_value && $orderValue >= $this->min_order_value) {
            return 0;
        }

        $tier = $this->tiers()
            ->where('min_value', '<=', $orderValue)
            ->where(fn ($q) => $q->whereNull('max_value')->orWhere('max_value', '>=', $orderValue))
            ->first();

        return $tier?->price ?? $this->price;
    }
}
