<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Page;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id') ?? Site::factory(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(5, true),
            'excerpt' => $this->faker->sentence(),
            'template' => $this->faker->randomElement(['default', 'full-width', 'sidebar']),
            'status' => $this->faker->randomElement(['published', 'draft', 'scheduled']),
            'meta_title' => $title,
            'meta_description' => $this->faker->sentence(),
            'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 year') : null,
        ];
    }

    public function published(): self
    {
        return $this->state(['status' => 'published', 'published_at' => now()]);
    }
}
