<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'type',
        'url',
        'is_default',
        'status',
        'locales',
        'currencies',
        'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'locales' => 'array',
        'currencies' => 'array',
        'settings' => 'array',
    ];

    // Relationships

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->active();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    // Accessors & Mutators

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setLocalesAttribute(?array $value): void
    {
        $this->attributes['locales'] = $value ? json_encode(array_values(array_unique($value))) : null;
    }

    public function setCurrenciesAttribute(?array $value): void
    {
        $this->attributes['currencies'] = $value ? json_encode(array_values(array_unique($value))) : null;
    }

    // Helpers

    public function supportsLocale(string $locale): bool
    {
        return in_array($locale, $this->locales ?? [], true);
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array($currency, $this->currencies ?? [], true);
    }

    public function getDefaultLocale(): ?string
    {
        return $this->locales[0] ?? $this->site?->locale;
    }

    public function getDefaultCurrency(): ?string
    {
        return $this->currencies[0] ?? $this->site?->default_currency;
    }
}
