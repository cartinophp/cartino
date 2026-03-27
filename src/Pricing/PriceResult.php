<?php

declare(strict_types=1);

namespace Cartino\Pricing;

use Illuminate\Support\Collection;

class PriceResult
{
    public function __construct(
        public float $originalPrice,
        public float $finalPrice,
        public Collection $appliedRules,
        public int $quantity = 1,
    ) {}

    /**
     * Get total discount amount
     */
    public function getDiscount(): float
    {
        return $this->originalPrice - $this->finalPrice;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if ($this->originalPrice == 0) {
            return 0;
        }

        return round(($this->getDiscount() / $this->originalPrice) * 100, 2);
    }

    /**
     * Check if price has been discounted
     */
    public function hasDiscount(): bool
    {
        return $this->finalPrice < $this->originalPrice;
    }

    /**
     * Get formatted original price
     */
    public function getFormattedOriginalPrice(): string
    {
        return money($this->originalPrice);
    }

    /**
     * Get formatted final price
     */
    public function getFormattedFinalPrice(): string
    {
        return money($this->finalPrice);
    }

    /**
     * Get total price for quantity
     */
    public function getTotalPrice(): float
    {
        return $this->finalPrice * $this->quantity;
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPrice(): string
    {
        return money($this->getTotalPrice());
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'original_price' => $this->originalPrice,
            'final_price' => $this->finalPrice,
            'discount' => $this->getDiscount(),
            'discount_percentage' => $this->getDiscountPercentage(),
            'quantity' => $this->quantity,
            'total_price' => $this->getTotalPrice(),
            'applied_rules' => $this->appliedRules->toArray(),
        ];
    }
}
