<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Price Rule Usage Model
 *
 * Tracks when and by whom a price rule was used.
 * Used for enforcing usage limits and analytics.
 *
 * @property int $id
 * @property int $price_rule_id
 * @property int $order_id
 * @property int|null $customer_id
 * @property float $discount_amount
 */
class PriceRuleUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_rule_id',
        'order_id',
        'customer_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function priceRule(): BelongsTo
    {
        return $this->belongsTo(PriceRule::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
