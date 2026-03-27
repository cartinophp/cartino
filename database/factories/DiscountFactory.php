<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed_amount', 'buy_x_get_y', 'free_shipping']);

        return [
            'code' => strtoupper($this->faker->unique()->bothify('????-####')),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'value' => $type === 'percentage'
                ? $this->faker->randomFloat(2, 1, 50)
                : $this->faker->randomFloat(2, 1, 100),
            'minimum_amount' => $this->faker->optional()->randomFloat(2, 10, 500),
            'maximum_discount_amount' => $this->faker->optional()->randomFloat(2, 10, 200),
            'usage_limit' => $this->faker->optional()->numberBetween(10, 1000),
            'usage_limit_per_customer' => $this->faker->optional()->numberBetween(1, 5),
            'usage_count' => $this->faker->numberBetween(0, 50),
            'starts_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'expires_at' => $this->faker->dateTimeBetween('now', '+6 months'),
            'is_active' => $this->faker->boolean(80),
            'target_type' => $this->faker->randomElement([
                'all',
                'specific_products',
                'specific_collections',
                'categories',
            ]),
            'target_selection' => null,
            'customer_eligibility' => $this->faker->randomElement(['all', 'specific_groups', 'specific_customers']),
            'customer_selection' => null,
            'shipping_countries' => null,
            'exclude_shipping_rates' => false,
            'admin_notes' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }
}
