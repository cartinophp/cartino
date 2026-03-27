<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'is_compound',
        'is_inclusive',
        'countries',
        'states',
        'postcodes',
        'product_categories',
        'min_amount',
        'max_amount',
        'is_enabled',
        'effective_from',
        'effective_until',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'countries' => 'array',
        'states' => 'array',
        'postcodes' => 'array',
        'product_categories' => 'array',
        'is_compound' => 'boolean',
        'is_inclusive' => 'boolean',
        'is_enabled' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Get the formatted rate as percentage
     */
    public function getFormattedRateAttribute(): string
    {
        if ($this->type === 'percentage') {
            return number_format($this->rate * 100, 2).'%';
        }

        return number_format($this->rate, 2);
    }

    /**
     * Check if tax rate is currently active
     */
    public function getIsActiveAttribute(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        $now = now()->toDateString();

        if ($this->effective_from && $this->effective_from > $now) {
            return false;
        }

        if ($this->effective_until && $this->effective_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Scope for enabled tax rates
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for active tax rates (enabled and within date range)
     */
    public function scopeActive($query)
    {
        $now = now()->toDateString();

        return $query
            ->where('is_enabled', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_until')->orWhere('effective_until', '>=', $now);
            });
    }
}
