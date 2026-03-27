<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\FidelityCard;
use Cartino\Models\FidelityTransaction;
use Cartino\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class FidelityTransactionFactory extends Factory
{
    protected $model = FidelityTransaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['earned', 'redeemed', 'expired', 'adjusted']);

        return [
            'fidelity_card_id' => FidelityCard::factory(),
            'order_id' => $type === 'earned' ? Order::factory() : null,
            'type' => $type,
            'points' => $this->getPointsForType($type),
            'description' => $this->getDescriptionForType($type),
            'expires_at' => $type === 'earned' ? $this->faker->dateTimeBetween('now', '+1 year') : null,
            'expired' => false,
            'reference_transaction_id' => null,
            'meta' => null,
        ];
    }

    public function earned(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'earned',
            'points' => $this->faker->numberBetween(1, 1000),
            'description' => 'Points earned from order #'.$this->faker->numberBetween(1000, 9999),
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'order_id' => Order::factory(),
        ]);
    }

    public function redeemed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'redeemed',
            'points' => -$this->faker->numberBetween(100, 1000),
            'description' => 'Points redeemed',
            'expires_at' => null,
            'order_id' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expired',
            'points' => -$this->faker->numberBetween(1, 500),
            'description' => 'Points expired',
            'expires_at' => null,
            'expired' => true,
        ]);
    }

    public function adjusted(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'adjusted',
            'points' => $this->faker->numberBetween(-500, 500),
            'description' => 'Manual adjustment',
            'expires_at' => null,
        ]);
    }

    private function getPointsForType(string $type): int
    {
        return match ($type) {
            'earned' => $this->faker->numberBetween(1, 1000),
            'redeemed' => -$this->faker->numberBetween(100, 1000),
            'expired' => -$this->faker->numberBetween(1, 500),
            'adjusted' => $this->faker->numberBetween(-500, 500),
            default => $this->faker->numberBetween(1, 1000),
        };
    }

    private function getDescriptionForType(string $type): string
    {
        return match ($type) {
            'earned' => 'Points earned from order #'.$this->faker->numberBetween(1000, 9999),
            'redeemed' => 'Points redeemed',
            'expired' => 'Points expired',
            'adjusted' => 'Manual adjustment',
            default => 'Transaction',
        };
    }
}
