<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Cart;
use Cartino\Models\Customer;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'session_id' => $this->faker->uuid(),
            'customer_id' => null,
            'email' => $this->faker->safeEmail(),
            'status' => $this->faker->randomElement(['active', 'abandoned']),
            'items' => null,
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'currency' => 'EUR',
            'last_activity_at' => now(),
            'shipping_address' => null,
            'billing_address' => null,
            'metadata' => null,
            'data' => null,
        ];
    }

    public function withCustomer(Customer $customer): static
    {
        return $this->state(fn () => [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'site_id' => $customer->site_id,
        ]);
    }
}
