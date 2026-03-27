<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        return [
            'event_type' => $this->faker->randomElement([
                'page_view',
                'product_view',
                'add_to_cart',
                'purchase',
                'search',
            ]),
            'user_id' => $this->faker->optional()->numberBetween(1, 100),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'url' => $this->faker->url(),
            'referrer' => $this->faker->optional()->url(),
            'properties' => [
                'product_id' => $this->faker->optional()->numberBetween(1, 1000),
                'category' => $this->faker->optional()->word(),
                'value' => $this->faker->optional()->randomFloat(2, 10, 500),
            ],
            'created_at' => $this->faker->dateTimeBetween('-6 months'),
        ];
    }
}
