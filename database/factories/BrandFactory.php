<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        $slug = Str::slug($name);

        return [
            'site_id' => null,
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'logo' => null,
            'status' => 'active',
            'seo' => [
                'title' => $name,
                'description' => $this->faker->sentence(),
                'keywords' => $this->faker->words(5, true),
            ],
            'data' => null,
        ];
    }
}
