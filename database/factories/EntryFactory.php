<?php

namespace Cartino\Database\Factories;

use Cartino\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryFactory extends Factory
{
    protected $model = Entry::class;

    public function definition(): array
    {
        return [
            'collection' => $this->faker->randomElement(['pages', 'blog', 'news']),
            'slug' => $this->faker->unique()->slug,
            'title' => $this->faker->sentence,
            'data' => [
                'content' => $this->faker->paragraphs(3, true),
                'excerpt' => $this->faker->sentence,
                'featured_image' => $this->faker->imageUrl(),
            ],
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'author_id' => null,
            'locale' => 'it',
            'parent_id' => null,
            'order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Published entry
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Draft entry
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Scheduled entry
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'published_at' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Page entry
     */
    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'collection' => 'pages',
            'data' => [
                'content' => $this->faker->paragraphs(5, true),
                'template' => 'default',
                'seo_title' => $this->faker->sentence,
                'seo_description' => $this->faker->sentence,
            ],
        ]);
    }

    /**
     * Blog post entry
     */
    public function blogPost(): static
    {
        return $this->state(fn (array $attributes) => [
            'collection' => 'blog',
            'data' => [
                'content' => $this->faker->paragraphs(8, true),
                'excerpt' => $this->faker->sentence,
                'featured_image' => $this->faker->imageUrl(1200, 630),
                'categories' => $this->faker->words(3),
                'tags' => $this->faker->words(5),
                'reading_time' => $this->faker->numberBetween(3, 15).' min',
            ],
        ]);
    }

    /**
     * With author
     */
    public function withAuthor(int $authorId): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $authorId,
        ]);
    }

    /**
     * With parent
     */
    public function withParent(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }
}
