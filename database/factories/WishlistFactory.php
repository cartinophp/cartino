<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    public function definition(): array
    {
        return [
            'customer_id' => fn () => Customer::factory(),
            'name' => $this->faker->randomElement(['Wishlist', 'Favorites', 'Gift Ideas']),
            'description' => $this->faker->sentence(8),
            'is_public' => $this->faker->boolean(10),
            'share_token' => Str::random(16),
            'metadata' => null,
        ];
    }
}
