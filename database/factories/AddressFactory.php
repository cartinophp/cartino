<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Address;
use Cartino\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'addressable_type' => null,
            'addressable_id' => null,
            'type' => 'shipping',
            'label' => 'Home',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company' => $this->faker->company(),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => null,
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country_id' => Country::query()->inRandomOrder()->value('id'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'formatted_address' => null,
            'place_id' => null,
            'is_validated' => false,
            'validated_at' => null,
            'validation_source' => null,
            'is_default' => true,
            'is_default_billing' => true,
            'is_default_shipping' => true,
            'metadata' => null,
            'notes' => null,
        ];
    }
}
