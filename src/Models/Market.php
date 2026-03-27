<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Support\HasHandle;
use Database\Factories\MarketFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use HasFactory;
    use HasHandle;
    use SoftDeletes;

    protected $fillable = [
        'handle',
        'name',
        'code',
        'description',
        'type',
        'countries',
        'default_currency',
        'supported_currencies',
        'default_locale',
        'supported_locales',
        'tax_included_in_prices',
        'tax_region',
        'catalog_id',
        'use_catalog_prices',
        'payment_methods',
        'shipping_methods',
        'fulfillment_locations',
        'priority',
        'is_default',
        'status',
        'order',
        'published_at',
        'unpublished_at',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'countries' => 'array',
        'supported_currencies' => 'array',
        'supported_locales' => 'array',
        'tax_included_in_prices' => 'boolean',
        'use_catalog_prices' => 'boolean',
        'payment_methods' => 'array',
        'shipping_methods' => 'array',
        'fulfillment_locations' => 'array',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'order' => 'integer',
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        // Prefer package factory
        if (class_exists(\Cartino\Database\Factories\MarketFactory::class)) {
            return \Cartino\Database\Factories\MarketFactory::new();
        }

        // Fallback to application factory namespace
        if (class_exists(MarketFactory::class)) {
            return MarketFactory::new();
        }

        throw new \RuntimeException('MarketFactory not found');
    }

    // Relations

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function activeSites(): HasMany
    {
        return $this->sites()->where('status', 'active');
    }

    public function publishedSites(): HasMany
    {
        return $this->sites()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('unpublished_at')->orWhere('unpublished_at', '>=', now());
            });
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('unpublished_at')->orWhere('unpublished_at', '>=', now());
            });
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->active()->orderByDesc('priority')->limit(1);
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) use ($countryCode) {
                $q->whereJsonContains('countries', $countryCode)->orWhereNull('countries'); // Global markets
            })
            ->orderByDesc('priority');
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('status', 'active')->where('type', $type)->orderByDesc('priority');
    }

    // Accessors

    public function getIsPublishedAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->unpublished_at && $this->unpublished_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getFormattedCodeAttribute(): string
    {
        return strtoupper($this->code);
    }

    // Helpers

    public function supportsCurrency(string $currency): bool
    {
        if (! $this->supported_currencies) {
            return $currency === $this->default_currency;
        }

        return in_array($currency, $this->supported_currencies);
    }

    public function supportsLocale(string $locale): bool
    {
        if (! $this->supported_locales) {
            return $locale === $this->default_locale;
        }

        return in_array($locale, $this->supported_locales);
    }

    public function supportsCountry(string $countryCode): bool
    {
        if (! $this->countries) {
            return true; // Global market
        }

        return in_array($countryCode, $this->countries);
    }

    public function supportsPaymentMethod(string $method): bool
    {
        if (! $this->payment_methods) {
            return true; // All methods allowed
        }

        return in_array($method, $this->payment_methods);
    }

    public function supportsShippingMethod(string $method): bool
    {
        if (! $this->shipping_methods) {
            return true; // All methods allowed
        }

        return in_array($method, $this->shipping_methods);
    }

    public function getCurrencies(): array
    {
        return $this->supported_currencies ?: [$this->default_currency];
    }

    public function getLocales(): array
    {
        return $this->supported_locales ?: [$this->default_locale];
    }

    public function getCountries(): array
    {
        return $this->countries ?: [];
    }

    // Static helpers

    public static function findByHandle(?string $handle = null): ?self
    {
        if ($handle) {
            return static::where('handle', $handle)->active()->first();
        }

        return static::default()->first();
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->active()->first();
    }

    public static function findForCountry(string $countryCode): ?self
    {
        return static::forCountry($countryCode)->first();
    }

    public function getRouteKeyName(): string
    {
        return 'handle';
    }
}
