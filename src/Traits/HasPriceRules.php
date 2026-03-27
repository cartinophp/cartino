<?php

declare(strict_types=1);

namespace Cartino\Traits;

/**
 * Trait HasPriceRules
 *
 * Provides pricing business logic and rules for products, variants, and orders.
 * Includes discounts, tier pricing, and special pricing logic.
 */
trait HasPriceRules
{
    /**
     * Calculate final price with discounts
     */
    public function calculateFinalPrice(?float $quantity = 1): float
    {
        $basePrice = $this->price ?? 0;

        // Apply tier pricing if available
        if (method_exists($this, 'getTierPrice')) {
            $tierPrice = $this->getTierPrice($quantity);
            if ($tierPrice) {
                $basePrice = $tierPrice;
            }
        }

        // Apply discount if compare_at_price is set
        if (isset($this->compare_at_price) && $this->compare_at_price > $basePrice) {
            return $basePrice;
        }

        return $basePrice;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): float
    {
        if (! isset($this->compare_at_price) || ! isset($this->price)) {
            return 0;
        }

        if ($this->compare_at_price <= $this->price) {
            return 0;
        }

        return $this->compare_at_price - $this->price;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if (! isset($this->compare_at_price) || ! isset($this->price)) {
            return 0;
        }

        if ($this->compare_at_price <= $this->price) {
            return 0;
        }

        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100, 2);
    }

    /**
     * Check if item is on sale
     */
    public function isOnSale(): bool
    {
        if (! isset($this->compare_at_price) || ! isset($this->price)) {
            return false;
        }

        return $this->compare_at_price > $this->price;
    }

    /**
     * Get savings amount
     */
    public function getSavings(): float
    {
        return $this->getDiscountAmount();
    }

    /**
     * Scope: Items on sale
     */
    public function scopeOnSale($query)
    {
        return $query->whereColumn('compare_at_price', '>', 'price')
            ->whereNotNull('compare_at_price');
    }

    /**
     * Scope: Price between range
     */
    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope: Price less than
     */
    public function scopePriceLessThan($query, float $price)
    {
        return $query->where('price', '<', $price);
    }

    /**
     * Scope: Price greater than
     */
    public function scopePriceGreaterThan($query, float $price)
    {
        return $query->where('price', '>', $price);
    }

    /**
     * Attribute: Is on sale
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->isOnSale();
    }

    /**
     * Attribute: Discount amount
     */
    public function getDiscountAmountAttribute(): float
    {
        return $this->getDiscountAmount();
    }

    /**
     * Attribute: Discount percentage
     */
    public function getDiscountPercentageAttribute(): float
    {
        return $this->getDiscountPercentage();
    }
}
