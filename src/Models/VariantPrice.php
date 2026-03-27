<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'site_id',
        'channel_id',
        'customer_group_id',
        'catalog_id',
        'currency',
        'price',
        'compare_at_price',
        'cost',
        'min_quantity',
        'max_quantity',
        'starts_at',
        'ends_at',
        'priority',
        'data',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'compare_at_price' => 'decimal:4',
        'cost' => 'decimal:4',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'priority' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'data' => 'array',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        });
    }

    public function scopeForContext($query, array $context)
    {
        if (isset($context['market_id'])) {
            $query->where('market_id', $context['market_id']);
        }

        if (isset($context['customer_group_id'])) {
            $query->where('customer_group_id', $context['customer_group_id']);
        }

        if (isset($context['currency'])) {
            $query->where('currency', $context['currency']);
        }

        if (isset($context['quantity'])) {
            $query
                ->where('min_quantity', '<=', $context['quantity'])
                ->where(fn ($q) => $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $context['quantity']));
        }

        return $query;
    }

    public function scopeByPriority($query)
    {
        return $query->orderByDesc('priority');
    }

    public function isActive(): bool
    {
        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }
}
