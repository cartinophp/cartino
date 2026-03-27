<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'description',
        'countries',
        'regions',
        'postal_codes',
        'priority',
        'is_active',
        'data',
    ];

    protected $casts = [
        'countries' => 'array',
        'regions' => 'array',
        'postal_codes' => 'array',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'data' => 'array',
    ];

    /**
     * Get shipping methods for this zone
     */
    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }

    /**
     * Get enabled shipping methods for this zone
     */
    public function enabledShippingMethods(): HasMany
    {
        return $this->shippingMethods()->where('is_enabled', true)->orderBy('sort_order');
    }

    /**
     * Check if zone covers a country
     */
    public function coversCountry(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), $this->countries ?? []);
    }

    /**
     * Check if zone covers a state in a country
     */
    public function coversState(string $countryCode, string $stateCode): bool
    {
        if (! $this->coversCountry($countryCode)) {
            return false;
        }

        // If no states specified, covers all states in the country
        if (empty($this->states)) {
            return true;
        }

        $stateKey = strtoupper($countryCode.'_'.$stateCode);

        return in_array($stateKey, $this->states);
    }

    /**
     * Check if zone covers a postcode
     */
    public function coversPostcode(string $postcode): bool
    {
        if (empty($this->postcodes)) {
            return true; // Covers all if not specified
        }

        foreach ($this->postcodes as $pattern) {
            if (fnmatch($pattern, $postcode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope for enabled zones
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
