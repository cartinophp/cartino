<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso3',
        'iso2',
        'phonecode',
        'capital',
        'currency_code',
        'currency_symbol',
        'tld',
        'native',
        'region',
        'subregion',
        'timezones',
        'translations',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
    ];

    protected $casts = [
        'timezones' => 'array',
        'translations' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function getRouteKeyName(): string
    {
        return 'iso2';
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
