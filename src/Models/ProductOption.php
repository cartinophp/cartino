<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'position',
        'is_global',
        'is_required',
        'is_visible',
        'use_for_variants',
        'configuration',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_global' => 'boolean',
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'use_for_variants' => 'boolean',
        'configuration' => 'array',
    ];

    // Relationships

    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class)->orderBy('position');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_option')
            ->withPivot('position', 'is_required')
            ->withTimestamps()
            ->orderBy('pivot_position');
    }

    // Scopes

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeForVariants($query)
    {
        return $query->where('use_for_variants', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
