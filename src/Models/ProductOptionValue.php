<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOptionValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_id',
        'label',
        'value',
        'color_hex',
        'image_url',
        'position',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_option_value')->withTimestamps();
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
