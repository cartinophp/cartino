<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Channel;
use Cartino\Models\CustomerGroup;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Cartino\Models\VariantPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantPriceFactory extends Factory
{
    protected $model = VariantPrice::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => fn () => ProductVariant::factory(),
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'channel_id' => Channel::query()->inRandomOrder()->value('id'),
            'customer_group_id' => CustomerGroup::query()->inRandomOrder()->value('id'),
            'catalog_id' => null,
            'currency' => 'EUR',
            'price' => $this->faker->randomFloat(4, 5, 200),
            'compare_at_price' => null,
            'cost' => $this->faker->randomFloat(4, 2, 150),
            'min_quantity' => 1,
            'max_quantity' => null,
            'starts_at' => now()->subDays(5),
            'ends_at' => null,
            'priority' => 0,
            'data' => null,
        ];
    }
}
