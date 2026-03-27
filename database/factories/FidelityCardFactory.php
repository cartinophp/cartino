<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\FidelityCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class FidelityCardFactory extends Factory
{
    protected $model = FidelityCard::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'total_points' => $this->faker->numberBetween(0, 5000),
            'available_points' => fn (array $attributes) => $this->faker->numberBetween(0, $attributes['total_points']),
            'total_earned' => fn (array $attributes) => (
                $attributes['total_points'] + $this->faker->numberBetween(0, 2000)
            ),
            'total_redeemed' => fn (array $attributes) => $attributes['total_earned'] - $attributes['total_points'],
            'total_spent_amount' => $this->faker->randomFloat(2, 0, 10000),
            'is_active' => true,
            'issued_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'last_activity_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'meta' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withHighPoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_points' => $this->faker->numberBetween(5000, 15000),
            'available_points' => fn (array $attributes) => $this->faker->numberBetween(
                1000,
                $attributes['total_points'],
            ),
            'total_spent_amount' => $this->faker->randomFloat(2, 5000, 50000),
        ]);
    }

    public function newCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_points' => 0,
            'available_points' => 0,
            'total_earned' => 0,
            'total_redeemed' => 0,
            'total_spent_amount' => 0,
            'issued_at' => now(),
            'last_activity_at' => null,
        ]);
    }
}
