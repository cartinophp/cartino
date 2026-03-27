<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::query()->inRandomOrder()->value('id') ?? Menu::factory(),
            'parent_id' => null,
            'title' => $this->faker->words(2, true),
            'url' => $this->faker->optional()->url(),
            'type' => $this->faker->randomElement(['link', 'collection', 'entry', 'external']),
            'reference_type' => null,
            'reference_id' => null,
            'data' => [],
            'status' => 'active',
            'opens_in_new_window' => $this->faker->boolean(10),
            'css_class' => $this->faker->optional()->word(),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'depth' => 0,
        ];
    }
}
