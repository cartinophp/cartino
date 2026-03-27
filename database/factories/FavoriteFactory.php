<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\Favorite;
use Cartino\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::query()->inRandomOrder()->value('id') ?? Customer::factory(),
            'favoriteable_type' => Product::class,
            'favoriteable_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
        ];
    }
}
