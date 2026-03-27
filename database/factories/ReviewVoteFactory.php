<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\ProductReview;
use Cartino\Models\ReviewVote;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewVoteFactory extends Factory
{
    protected $model = ReviewVote::class;

    public function definition(): array
    {
        return [
            'product_review_id' => ProductReview::query()->inRandomOrder()->value('id') ?? ProductReview::factory(),
            'customer_id' => Customer::query()->inRandomOrder()->value('id') ?? Customer::factory(),
            'vote' => $this->faker->randomElement(['helpful', 'unhelpful']),
        ];
    }
}
