<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\HasCustomFields;
use Cartino\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasCustomFields;
    use HasFactory;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'track_quantity',
        'stock_status',
        'weight',
        'dimensions',
        'option_values',
        'status',
        'sort_order',
        'data',
    ];

    protected array $translatable = [
        'name',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_quantity' => 'boolean',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'option_values' => 'array',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartLines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }
}
