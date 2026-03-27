<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        $couriers = [
            'DHL Express',
            'FedEx',
            'UPS',
            'TNT',
            'GLS',
            'BRT',
            'SDA Express Courier',
            'Poste Italiane',
            'DPD',
            'Nexive',
        ];

        $name = $this->faker->unique()->randomElement($couriers);
        $slug = Str::slug($name);
        $code = strtoupper(Str::slug($name, '_'));

        return [
            'site_id' => null,
            'name' => $name,
            'slug' => $slug,
            'code' => $code,
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'tracking_url' => $this->faker->url().'/tracking/{tracking_number}',
            'logo' => null,
            'delivery_time_min' => $this->faker->numberBetween(1, 3),
            'delivery_time_max' => $this->faker->numberBetween(4, 7),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'is_enabled' => $this->faker->boolean(80),
            'seo' => [
                'title' => $name,
                'description' => $this->faker->sentence(),
                'keywords' => $this->faker->words(5, true),
            ],
            'meta' => [
                'support_phone' => $this->faker->phoneNumber(),
                'support_email' => $this->faker->email(),
            ],
            'data' => null,
        ];
    }

    /**
     * Indicate that the courier is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the courier is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
            'status' => 'inactive',
        ]);
    }
}
