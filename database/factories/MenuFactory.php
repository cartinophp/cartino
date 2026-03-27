<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Menu;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::query()->inRandomOrder()->value('id') ?? Site::factory(),
            'title' => $this->faker->unique()->words(2, true),
            'handle' => $this->faker->unique()->slug(2),
            'location' => $this->faker->randomElement(['header', 'footer', 'sidebar', 'mobile']),
            'settings' => [
                'max_depth' => $this->faker->numberBetween(1, 5),
                'show_icons' => $this->faker->boolean(),
            ],
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
