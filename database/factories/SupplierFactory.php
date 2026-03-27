<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Site;
use Cartino\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::query()->inRandomOrder()->value('id') ?? Site::factory(),
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->unique()->bothify('SUP-###??')),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->optional()->paragraph(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'mobile' => $this->faker->optional()->phoneNumber(),
            'fax' => $this->faker->optional()->phoneNumber(),
            'website' => $this->faker->optional()->url(),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => strtoupper($this->faker->countryCode()),
            'tax_number' => $this->faker->numerify('##########'),
            'company_registration' => strtoupper($this->faker->bothify('CR-#####')),
            'credit_limit' => $this->faker->optional()->randomFloat(2, 1000, 50000),
            'payment_terms_days' => $this->faker->numberBetween(15, 90),
            'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'status' => 'active',
            'is_preferred' => $this->faker->boolean(10),
            'priority' => $this->faker->numberBetween(0, 10),
            'minimum_order_amount' => $this->faker->optional()->randomFloat(2, 100, 1000),
            'lead_time_days' => $this->faker->optional()->numberBetween(3, 60),
            'notes' => $this->faker->optional()->paragraph(),
            'metadata' => [],
            'certifications' => [],
            'rating' => $this->faker->optional()->randomFloat(2, 1, 5),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }
}
