<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        $code = 'TAX'.Str::upper(Str::random(5));

        return [
            'name' => 'VAT '.$this->faker->numberBetween(10, 25).'%',
            'code' => $code,
            'rate' => $this->faker->randomFloat(4, 0.05, 0.22),
            'type' => 'percentage',
            'is_compound' => false,
            'is_inclusive' => false,
            'countries' => ['IT'],
            'states' => null,
            'postcodes' => null,
            'product_collections' => null,
            'min_amount' => null,
            'max_amount' => null,
            'status' => 'active',
            'effective_from' => now()->subMonth(),
            'effective_until' => null,
            'description' => 'Standard VAT rate',
        ];
    }
}
