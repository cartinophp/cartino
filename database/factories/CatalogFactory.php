<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Catalog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CatalogFactory extends Factory
{
    protected $model = Catalog::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);
        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'adjustment_type' => $this->faker->randomElement(['percentage', 'fixed', null]),
            'adjustment_direction' => $this->faker->randomElement(['increase', 'decrease', null]),
            'adjustment_value' => $this->faker->randomFloat(2, 0, 100),
            'auto_include_new_products' => $this->faker->boolean(30),
            'is_default' => false,
            'status' => $this->faker->randomElement(['active', 'draft']),
            'published_at' => $this->faker->boolean(70) ? now() : null,
            'data' => [],
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }

    public function published(): self
    {
        return $this->state(['status' => 'active', 'published_at' => now()]);
    }

    public function default(): self
    {
        return $this->state(['is_default' => true, 'status' => 'active']);
    }
}
