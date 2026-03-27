<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        return [
            'site_id' => null,
            'name' => $this->faker->randomElement([
                'Retail Customers',
                'Wholesale',
                'VIP Members',
                'Business Partners',
                'Premium Club',
                'Regular Members',
            ]),
            'slug' => $this->faker->slug(2),
            'description' => $this->faker->sentence(),
            'is_default' => false,
            'is_enabled' => true,
            'discount_percentage' => $this->faker->randomFloat(2, 0, 25),
            'tax_exempt' => false,
            'pricing_rules' => null,
            'permissions' => null,
            'restrictions' => null,
            'status' => 'active',
            'data' => null,
        ];
    }

    public function default(): self
    {
        return $this->state(['is_default' => true, 'discount_percentage' => 0]);
    }
}
