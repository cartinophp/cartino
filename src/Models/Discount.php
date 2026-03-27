<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'usage_count',
        'is_enabled',
        'starts_at',
        'expires_at',
        'eligible_products',
        'eligible_categories',
        'eligible_customers',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'usage_count' => 'integer',
        'is_enabled' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'eligible_products' => 'array',
        'eligible_categories' => 'array',
        'eligible_customers' => 'array',
    ];

    protected $dates = [
        'starts_at',
        'expires_at',
        'deleted_at',
    ];

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED_AMOUNT = 'fixed_amount';

    public const TYPE_FREE_SHIPPING = 'free_shipping';

    public const TYPES = [
        self::TYPE_PERCENTAGE,
        self::TYPE_FIXED_AMOUNT,
        self::TYPE_FREE_SHIPPING,
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Scope a query to only include active discounts.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_enabled', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope a query to only include expired discounts.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope a query to only include discounts that haven't started yet.
     */
    public function scopeScheduled($query)
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Scope a query to only include discounts by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if the discount is currently active.
     */
    public function isActive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->isAfter($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (! $this->isActive()) {
            return 0;
        }

        if ($this->minimum_order_amount && $amount < $this->minimum_order_amount) {
            return 0;
        }

        $discount = match ($this->type) {
            'percentage' => $amount * ($this->value / 100),
            'fixed_amount' => $this->value,
            'free_shipping' => 0, // Handle shipping discount separately
            default => 0,
        };

        if ($this->maximum_discount_amount && $discount > $this->maximum_discount_amount) {
            $discount = $this->maximum_discount_amount;
        }

        return round($discount, 2);
    }

    /**
     * Check if the discount is applicable to a specific customer.
     */
    public function isApplicableToCustomer($customerId): bool
    {
        if (empty($this->eligible_customers)) {
            return true;
        }

        return in_array($customerId, $this->eligible_customers);
    }

    /**
     * Check if the discount is applicable to a specific product.
     */
    public function isApplicableToProduct($productId): bool
    {
        if (empty($this->eligible_products)) {
            return true;
        }

        return in_array($productId, $this->eligible_products);
    }

    /**
     * Check if the discount is applicable to a specific category.
     */
    public function isApplicableToCategory($categoryId): bool
    {
        if (empty($this->eligible_categories)) {
            return true;
        }

        return in_array($categoryId, $this->eligible_categories);
    }

    /**
     * Check if customer can use this discount (usage limit per customer).
     */
    public function canCustomerUse($customerId): bool
    {
        if (! $this->usage_limit_per_customer) {
            return true;
        }

        // In a real implementation, you'd count actual usage from orders/usage tracking table
        return true; // Placeholder - implement usage tracking if needed
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Check if discount has free shipping.
     */
    public function isFreeShipping(): bool
    {
        return $this->type === self::TYPE_FREE_SHIPPING;
    }

    /**
     * Check if discount is percentage type.
     */
    public function isPercentage(): bool
    {
        return $this->type === self::TYPE_PERCENTAGE;
    }

    /**
     * Check if discount is fixed amount type.
     */
    public function isFixedAmount(): bool
    {
        return $this->type === self::TYPE_FIXED_AMOUNT;
    }

    /**
     * Get formatted discount value for display.
     */
    public function getFormattedValueAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PERCENTAGE => $this->value.'%',
            self::TYPE_FIXED_AMOUNT => '€'.number_format($this->value, 2),
            self::TYPE_FREE_SHIPPING => 'Free Shipping',
            default => (string) $this->value,
        };
    }

    /**
     * Get discount status for display.
     */
    public function getStatusAttribute(): string
    {
        if (! $this->is_enabled) {
            return 'disabled';
        }

        $now = now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return 'scheduled';
        }

        if ($this->expires_at && $now->isAfter($this->expires_at)) {
            return 'expired';
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }
}
