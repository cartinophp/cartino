<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\ProductReview;
use Cartino\Models\ReviewMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewMediaFactory extends Factory
{
    protected $model = ReviewMedia::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['image', 'video']);

        return [
            'product_review_id' => ProductReview::query()->inRandomOrder()->value('id') ?? ProductReview::factory(),
            'type' => $type,
            'url' => $type === 'image' ? $this->faker->imageUrl() : $this->faker->url(),
            'thumbnail_url' => $this->faker->imageUrl(200, 200),
            'mime_type' => $type === 'image' ? 'image/jpeg' : 'video/mp4',
            'size' => $this->faker->numberBetween(100000, 5000000),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
