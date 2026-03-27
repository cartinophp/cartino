<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Enums\WishlistStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'customer_id',
        'is_public',
        'is_default',
        'share_token',
        'meta',
    ];

    protected $casts = [
        'status' => WishlistStatus::class,
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($wishlist) {
            if (empty($wishlist->share_token)) {
                $wishlist->share_token = Str::random(32);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function addItem(
        Product $product,
        ?ProductVariant $variant = null,
        array $options = [],
        ?string $note = null,
    ): WishlistItem {
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            return $existingItem;
        }

        return $this->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_options' => $options,
            'note' => $note,
        ]);
    }

    public function removeItem(Product $product, ?ProductVariant $variant = null): bool
    {
        return $this->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->delete() > 0;
    }

    public function hasItem(Product $product, ?ProductVariant $variant = null): bool
    {
        return $this->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->exists();
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->count();
    }

    public function getShareUrlAttribute(): string
    {
        return route('wishlists.shared', $this->share_token);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
