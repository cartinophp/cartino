<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Product;
use Cartino\Models\Wishlist;
use Cartino\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        return [
            'wishlist_id' => fn () => Wishlist::factory(),
            'product_type' => 'entry',
            'product_id' => fn () => Product::factory(),
            'product_handle' => null,
            'product_data' => null,
            'variant_data' => null,
            'quantity' => 1,
            'price_at_time' => $this->faker->randomFloat(2, 10, 200),
            'note' => $this->faker->optional()->sentence(6),
        ];
    }
}
