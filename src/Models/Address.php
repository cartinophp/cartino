<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_id',
        'phone',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line_1;

        if ($this->address_line_2) {
            $address .= ', '.$this->address_line_2;
        }

        $address .= ', '.$this->city;

        if ($this->state) {
            $address .= ', '.$this->state;
        }

        if ($this->postal_code) {
            $address .= ' '.$this->postal_code;
        }

        $address .= ', '.$this->country->name;

        return $address;
    }
}
