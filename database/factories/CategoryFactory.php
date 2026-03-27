<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Category;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        $slug = Str::slug($name);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'parent_id' => null,
            'level' => 0,
            'path' => $slug,
            'left' => 1,
            'right' => 2,
            'name' => ucfirst($name),
            'slug' => $slug,
            'description' => $this->faker->sentence(10),
            'short_description' => $this->faker->sentence(6),
            'sort_order' => $this->faker->numberBetween(0, 20),
            'icon' => null,
            'color' => null,
            'is_featured' => $this->faker->boolean(30),
            'meta_title' => ucfirst($name),
            'meta_description' => $this->faker->sentence(12),
            'seo' => null,
            'is_active' => true,
            'is_visible' => true,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 15)),
            'include_in_menu' => true,
            'include_in_search' => true,
            'products_count' => 0,
            'template' => null,
            'layout_settings' => null,
            'data' => null,
        ];
    }

    public function child(Category $parent, int $left, int $right): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'level' => $parent->level + 1,
            'path' => $parent->path.'/'.$this->faker->slug(),
            'left' => $left,
            'right' => $right,
        ]);
    }
}
