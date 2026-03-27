<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'product_variant_id',
        'product_options',
        'note',
    ];

    protected $casts = [
        'product_options' => 'array',
    ];

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;

        if ($this->variant) {
            $name .= ' - '.$this->variant->name;
        }

        return $name;
    }

    public function getPriceAttribute(): float
    {
        return $this->variant ? $this->variant->price : $this->product->price;
    }
}
