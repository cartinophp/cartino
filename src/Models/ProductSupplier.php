<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSupplier extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'supplier_id',
        'supplier_sku',
        'cost_price',
        'currency',
        'minimum_order_quantity',
        'lead_time_days',
        'price_tiers',
        'supplier_name',
        'supplier_description',
        'manufacturer_part_number',
        'barcode',
        'is_primary',
        'status',
        'priority',
        'quality_rating',
        'delivery_rating',
        'order_count',
        'last_ordered_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'minimum_order_quantity' => 'integer',
        'lead_time_days' => 'integer',
        'priority' => 'integer',
        'order_count' => 'integer',
        'quality_rating' => 'decimal:2',
        'delivery_rating' => 'decimal:2',
        'is_primary' => 'boolean',
        'price_tiers' => 'array',
        'last_ordered_at' => 'datetime',
    ];

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by primary suppliers.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Get the price for a given quantity.
     */
    public function getPriceForQuantity(int $quantity): float
    {
        if (empty($this->price_tiers)) {
            return (float) $this->cost_price;
        }

        $bestPrice = (float) $this->cost_price;

        foreach ($this->price_tiers as $tier) {
            if ($quantity >= $tier['min_qty'] && $tier['price'] < $bestPrice) {
                $bestPrice = (float) $tier['price'];
            }
        }

        return $bestPrice;
    }

    /**
     * Check if this is the primary supplier for the product.
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Set as primary supplier (removes primary from others).
     */
    public function setAsPrimary(): void
    {
        // Remove primary status from other suppliers for this product
        static::where('product_id', $this->product_id)->where('id', '!=', $this->id)->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true]);
    }
}
