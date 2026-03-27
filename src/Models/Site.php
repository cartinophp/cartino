<?php

namespace Cartino\Models;

use Cartino\Support\HasHandle;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory;
    use HasHandle;
    use SoftDeletes;

    protected $fillable = [
        'market_id',
        'handle',
        'name',
        'description',
        'url',
        'domain',
        'domains',
        'locale',
        'lang',
        'countries',
        'default_currency',
        'tax_included_in_prices',
        'tax_region',
        'priority',
        'is_default',
        'status',
        'order',
        'published_at',
        'unpublished_at',
        'attributes',
    ];

    protected $casts = [
        'domains' => 'array',
        'countries' => 'array',
        'tax_included_in_prices' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'order' => 'integer',
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
        'attributes' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     * /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        // Prefer package factory
        if (class_exists(\Cartino\Database\Factories\SiteFactory::class)) {
            return \Cartino\Database\Factories\SiteFactory::new();
        }

        // Fallback to application factory namespace
        if (class_exists(SiteFactory::class)) {
            return SiteFactory::new();
        }

        throw new \RuntimeException('SiteFactory not found');
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function catalogs(): BelongsToMany
    {
        return $this->belongsToMany(Catalog::class, 'site_catalog')
            ->withPivot(['priority', 'is_default', 'is_active', 'starts_at', 'ends_at', 'settings'])
            ->withTimestamps();
    }

    public function activeCatalogs(): BelongsToMany
    {
        return $this->catalogs()->wherePivot('is_active', true)->orderByPivot('priority', 'desc');
    }

    public function defaultCatalog(): BelongsToMany
    {
        return $this->catalogs()
            ->wherePivot('is_default', true)
            ->wherePivot('is_active', true)
            ->limit(1);
    }

    public function shippingZones(): HasMany
    {
        return $this->hasMany(ShippingZone::class);
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
                $q->whereJsonContains('countries', $countryCode)->orWhereNull('countries'); // Global sites
            })
            ->orderByDesc('priority');
    }

    public function scopeForDomain($query, string $domain)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) use ($domain) {
                $q->where('domain', $domain)->orWhereJsonContains('domains', $domain);
            });
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

    public function getSupportedCurrenciesAttribute(): array
    {
        // Get unique currencies from all active channels
        $currencies = $this->channels()
            ->where('status', 'active')
            ->get()
            ->pluck('currencies')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->all();

        return $currencies ?: [$this->default_currency];
    }

    public function getSupportedLocalesAttribute(): array
    {
        // Get unique locales from all active channels
        $locales = $this->channels()
            ->where('status', 'active')
            ->get()
            ->pluck('locales')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->all();

        return $locales ?: [$this->locale];
    }

    // Static helpers

    public static function findByHandle(?string $handle = null): ?self
    {
        if ($handle) {
            return static::where('handle', $handle)->active()->first();
        }

        return static::default()->first();
    }

    public static function findForDomain(string $domain): ?self
    {
        return static::forDomain($domain)->first();
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
