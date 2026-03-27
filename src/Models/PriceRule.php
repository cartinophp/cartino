<?php

declare(strict_types=1);

namespace Cartino\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Price Rule Model
 *
 * Represents a dynamic pricing rule that can apply discounts/overrides
 * based on complex conditions (customer groups, channels, quantity, etc.).
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property int $priority
 * @property string $entity_type
 * @property array|null $entity_ids
 * @property array|null $conditions
 * @property string $discount_type
 * @property float $discount_value
 * @property bool $stop_further_rules
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property int|null $usage_limit
 * @property int|null $usage_limit_per_customer
 * @property int $usage_count
 */
class PriceRule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'priority',
        'entity_type',
        'entity_ids',
        'conditions',
        'discount_type',
        'discount_value',
        'stop_further_rules',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_limit_per_customer',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'entity_ids' => 'array',
        'conditions' => 'array',
        'discount_value' => 'decimal:4',
        'stop_further_rules' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'usage_count' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'priority' => 0,
        'entity_type' => 'product',
        'discount_type' => 'percent',
        'stop_further_rules' => false,
        'usage_count' => 0,
    ];

    // ========================================
    // Relationships
    // ========================================

    /**
     * Track how many times this rule has been used.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(PriceRuleUsage::class);
    }

    // ========================================
    // Query Scopes
    // ========================================

    /**
     * Only active rules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Rules within their valid time range.
     */
    public function scopeWithinTimeRange(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }

    /**
     * Rules that haven't reached usage limit.
     */
    public function scopeWithinUsageLimit(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')->orWhereRaw('usage_count < usage_limit');
        });
    }

    /**
     * Order by priority (higher first).
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Filter by entity type.
     */
    public function scopeForEntityType(Builder $query, string $entityType): Builder
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Filter by discount type.
     */
    public function scopeByDiscountType(Builder $query, string $discountType): Builder
    {
        return $query->where('discount_type', $discountType);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if rule is currently valid (active, within time, under limits).
     */
    public function isValid(): bool
    {
        // Check if active
        if (! $this->is_active) {
            return false;
        }

        // Check time range
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if rule can still be used by a customer.
     */
    public function canBeUsedBy(?int $customerId): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        // Check per-customer limit
        if ($customerId && $this->usage_limit_per_customer) {
            $usageCount = $this->usages()->where('customer_id', $customerId)->count();

            if ($usageCount >= $this->usage_limit_per_customer) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get formatted discount for display.
     */
    public function getFormattedDiscountAttribute(): string
    {
        return match ($this->discount_type) {
            'percent' => "{$this->discount_value}%",
            'fixed' => currency_format($this->discount_value),
            'override' => currency_format($this->discount_value).' (override)',
            default => $this->discount_value,
        };
    }

    /**
     * Get the number of times this rule can still be used globally.
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if (! $this->usage_limit) {
            return null; // Unlimited
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    /**
     * Check if rule applies to specific entities.
     */
    public function appliesToEntity(int $entityId, string $entityType): bool
    {
        if ($this->entity_type !== $entityType) {
            return false;
        }

        // If entity_ids is null/empty, applies to all
        if (empty($this->entity_ids)) {
            return true;
        }

        return in_array($entityId, $this->entity_ids);
    }

    /**
     * Check if rule has specific condition.
     */
    public function hasCondition(string $key): bool
    {
        return isset($this->conditions[$key]);
    }

    /**
     * Get condition value.
     */
    public function getCondition(string $key, $default = null)
    {
        return $this->conditions[$key] ?? $default;
    }

    /**
     * Calculate discount amount for a given price.
     */
    public function calculateDiscount(float $price): float
    {
        return match ($this->discount_type) {
            'percent' => $price * ($this->discount_value / 100),
            'fixed' => min($price, $this->discount_value),
            'override' => max(0, $price - $this->discount_value),
            default => 0,
        };
    }

    /**
     * Apply this rule to a price and get the final price.
     */
    public function applyToPrice(float $price): float
    {
        return match ($this->discount_type) {
            'percent' => $price * (1 - ($this->discount_value / 100)),
            'fixed' => max(0, $price - $this->discount_value),
            'override' => $this->discount_value,
            default => $price,
        };
    }
}
