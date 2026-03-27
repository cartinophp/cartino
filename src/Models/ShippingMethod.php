<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'name',
        'description',
        'type',
        'cost',
        'free_shipping_threshold',
        'min_delivery_days',
        'max_delivery_days',
        'weight_limit',
        'size_limit',
        'requires_address',
        'is_enabled',
        'sort_order',
        'configuration',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'weight_limit' => 'decimal:2',
        'size_limit' => 'decimal:2',
        'requires_address' => 'boolean',
        'is_enabled' => 'boolean',
        'configuration' => 'array',
    ];

    /**
     * Get the shipping zone this method belongs to
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Get the delivery time range as string
     */
    public function getDeliveryTimeAttribute(): ?string
    {
        if (! $this->min_delivery_days && ! $this->max_delivery_days) {
            return null;
        }

        if ($this->min_delivery_days && $this->max_delivery_days) {
            if ($this->min_delivery_days === $this->max_delivery_days) {
                return $this->min_delivery_days.' giorni';
            }

            return $this->min_delivery_days.'-'.$this->max_delivery_days.' giorni';
        }

        if ($this->min_delivery_days) {
            return 'Minimo '.$this->min_delivery_days.' giorni';
        }

        return 'Massimo '.$this->max_delivery_days.' giorni';
    }

    /**
     * Calculate shipping cost for an order
     */
    public function calculateCost(float $orderTotal, ?float $totalWeight = null): float
    {
        switch ($this->type) {
            case 'free':
                return 0;

            case 'flat_rate':
                // Check if order qualifies for free shipping
                if ($this->free_shipping_threshold && $orderTotal >= $this->free_shipping_threshold) {
                    return 0;
                }

                return $this->cost;

            case 'calculated':
                // This would integrate with carrier APIs
                // For now, return base cost
                return $this->cost;

            case 'pickup':
                return 0;

            default:
                return $this->cost;
        }
    }

    /**
     * Check if method can handle the weight/size
     */
    public function canHandle(?float $weight = null, ?float $size = null): bool
    {
        if ($this->weight_limit && $weight && $weight > $this->weight_limit) {
            return false;
        }

        if ($this->size_limit && $size && $size > $this->size_limit) {
            return false;
        }

        return true;
    }

    /**
     * Get the type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'flat_rate' => 'Tariffa fissa',
            'free' => 'Gratuita',
            'calculated' => 'Calcolata',
            'pickup' => 'Ritiro in negozio',
            default => ucfirst($this->type),
        };
    }

    /**
     * Scope for enabled methods
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for ordering by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
