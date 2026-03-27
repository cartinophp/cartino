<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $currencyData = $this->faker->randomElement([
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
        ]);

        return [
            'name' => $currencyData['name'],
            'code' => $currencyData['code'],
            'symbol' => $currencyData['symbol'],
            'rate' => $this->faker->randomFloat(4, 0.5, 2.0),
            'is_default' => false,
        ];
    }

    public function default(): self
    {
        return $this->state(['is_default' => true, 'rate' => 1.0000]);
    }
}
