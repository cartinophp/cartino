<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $countryCode = $this->faker->unique()->countryCode();

        return [
            'name' => $this->faker->country(),
            'code' => $countryCode,
            'code_alpha3' => null,
            'phone_code' => (string) $this->faker->numberBetween(1, 999),
            'currency' => $this->faker->currencyCode(),
            'continent' => $this->faker->randomElement(['AF', 'AS', 'EU', 'NA', 'OC', 'SA', 'AN']),
            'timezones' => [
                [
                    'zoneName' => $this->faker->timezone(),
                ],
            ],
            'requires_state' => $this->faker->boolean(30),
            'requires_postal_code' => $this->faker->boolean(80),
            'postal_code_format' => null,
            'status' => 'active',
            'metadata' => null,
            'is_enabled' => true,
        ];
    }
}
