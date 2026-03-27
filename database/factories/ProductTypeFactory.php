<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductTypeFactory extends Factory
{
    protected $model = ProductType::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Physical',
            'Digital',
            'Service',
            'Subscription',
            'Downloadable',
            'Virtual',
        ]);
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }
}
