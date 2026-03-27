<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\Product;
use Cartino\Models\ProductReview;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
            'customer_id' => Customer::query()->inRandomOrder()->value('id') ?? Customer::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'pros' => $this->faker->optional()->sentence(),
            'cons' => $this->faker->optional()->sentence(),
            'verified_purchase' => $this->faker->boolean(70),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'is_featured' => $this->faker->boolean(10),
            'helpful_count' => $this->faker->numberBetween(0, 50),
            'unhelpful_count' => $this->faker->numberBetween(0, 10),
            'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 year') : null,
        ];
    }

    public function approved(): self
    {
        return $this->state(['status' => 'approved', 'published_at' => now()]);
    }

    public function featured(): self
    {
        return $this->state(['is_featured' => true, 'status' => 'approved']);
    }
}
