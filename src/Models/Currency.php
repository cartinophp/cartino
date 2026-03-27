<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'rate',
        'precision',
        'is_default',
        'is_enabled',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'precision' => 'integer',
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
