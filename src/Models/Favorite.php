<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'favoriteable_type',
        'favoriteable_id',
        'customer_id',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function favoriteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function toggle($favoriteableType, $favoriteableId, $customerId, string $type = 'product'): bool
    {
        $favorite = static::where([
            'favoriteable_type' => $favoriteableType,
            'favoriteable_id' => $favoriteableId,
            'customer_id' => $customerId,
        ])->first();

        if ($favorite) {
            $favorite->delete();

            return false; // Removed from favorites
        }

        static::create([
            'favoriteable_type' => $favoriteableType,
            'favoriteable_id' => $favoriteableId,
            'customer_id' => $customerId,
            'type' => $type,
        ]);

        return true; // Added to favorites
    }

    public static function isFavorited($favoriteableType, $favoriteableId, $customerId): bool
    {
        return static::where([
            'favoriteable_type' => $favoriteableType,
            'favoriteable_id' => $favoriteableId,
            'customer_id' => $customerId,
        ])->exists();
    }

    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeBrands($query)
    {
        return $query->where('type', 'brand');
    }

    public function scopeCategories($query)
    {
        return $query->where('type', 'category');
    }
}
