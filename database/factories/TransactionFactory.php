<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Order;
use Cartino\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'success', 'failed', 'refunded']);

        return [
            'order_id' => Order::query()->inRandomOrder()->value('id') ?? Order::factory(),
            'transaction_id' => $this->faker->unique()->uuid(),
            'gateway' => $this->faker->randomElement(['stripe', 'paypal', 'bank_transfer']),
            'type' => $this->faker->randomElement(['payment', 'refund', 'capture', 'void']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->currencyCode(),
            'status' => $status,
            'response' => [
                'success' => $status === 'success',
                'message' => $this->faker->sentence(),
            ],
            'metadata' => [
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],
            'processed_at' => $status === 'success' ? $this->faker->dateTimeThisYear() : null,
        ];
    }

    public function successful(): self
    {
        return $this->state(['status' => 'success', 'processed_at' => now()]);
    }
}
