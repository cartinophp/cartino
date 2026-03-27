<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\ProductVariant;
use Cartino\Models\StockNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockNotificationFactory extends Factory
{
    protected $model = StockNotification::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::query()->inRandomOrder()->value('id') ?? ProductVariant::factory(),
            'customer_id' => Customer::query()->inRandomOrder()->value('id') ?? Customer::factory(),
            'email' => $this->faker->email(),
            'status' => $this->faker->randomElement(['pending', 'sent', 'cancelled']),
            'notified_at' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
