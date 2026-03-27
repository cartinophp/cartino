<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentMethodFactory extends Factory
{
    protected $model = Model::class;

    public function definition(): array
    {
        $provider = $this->faker->randomElement(['stripe', 'paypal', 'bank_transfer', 'cash_on_delivery']);
        $name = ucfirst($provider).' '.$this->faker->randomElement(['Card', 'Payment', 'Transfer']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'provider' => $provider,
            'description' => $this->faker->sentence(),
            'configuration' => [
                'mode' => $this->faker->randomElement(['test', 'live']),
                'api_key' => $this->faker->uuid(),
            ],
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'is_test_mode' => $this->faker->boolean(80),
            'fixed_fee' => $this->faker->randomFloat(2, 0, 1),
            'percentage_fee' => $this->faker->randomFloat(4, 0.01, 0.05),
            'supported_currencies' => $this->faker->randomElements(['EUR', 'USD', 'GBP'], 2),
            'supported_countries' => $this->faker->randomElements(['IT', 'FR', 'DE', 'ES', 'US'], 3),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }
}
