<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_variant_id',
        'market_id',
        'site_id',
        'channel_id',
        'price_list_id',
        'currency',
        'amount',
        'compare_at_amount',
        'cost_amount',
        'tax_included',
        'tax_rate',
        'min_quantity',
        'max_quantity',
        'starts_at',
        'ends_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'compare_at_amount' => 'integer',
        'cost_amount' => 'integer',
        'tax_included' => 'boolean',
        'tax_rate' => 'decimal:4',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_amount',
        'formatted_compare_at',
        'discount_percentage',
    ];

    // Relationships

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    // Accessors

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }

    public function getFormattedCompareAtAttribute(): ?string
    {
        return $this->compare_at_amount ? number_format($this->compare_at_amount / 100, 2) : null;
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        if (! $this->compare_at_amount || $this->compare_at_amount <= $this->amount) {
            return null;
        }

        return round((($this->compare_at_amount - $this->amount) / $this->compare_at_amount) * 100, 2);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForMarket($query, ?int $marketId)
    {
        return $query->where(function ($q) use ($marketId) {
            $q->where('market_id', $marketId)->orWhereNull('market_id');
        });
    }

    public function scopeForSite($query, ?int $siteId)
    {
        return $query->where(function ($q) use ($siteId) {
            $q->where('site_id', $siteId)->orWhereNull('site_id');
        });
    }

    public function scopeForChannel($query, ?int $channelId)
    {
        return $query->where(function ($q) use ($channelId) {
            $q->where('channel_id', $channelId)->orWhereNull('channel_id');
        });
    }

    public function scopeForCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeForQuantity($query, int $quantity)
    {
        return $query
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            });
    }

    // Methods

    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getAmountWithTax(): int
    {
        if ($this->tax_included || ! $this->tax_rate) {
            return $this->amount;
        }

        return (int) round($this->amount * (1 + ($this->tax_rate / 100)));
    }

    public function getAmountWithoutTax(): int
    {
        if (! $this->tax_included || ! $this->tax_rate) {
            return $this->amount;
        }

        return (int) round($this->amount / (1 + ($this->tax_rate / 100)));
    }
}
