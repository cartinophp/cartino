<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['string', 'boolean', 'integer', 'json']);
        $value = match ($type) {
            'boolean' => $this->faker->boolean() ? '1' : '0',
            'integer' => (string) $this->faker->numberBetween(1, 100),
            'json' => json_encode([$this->faker->word() => $this->faker->word()]),
            default => $this->faker->sentence(),
        };

        return [
            'key' => $this->faker->unique()->slug(2),
            'value' => $value,
            'type' => $type,
            'group' => $this->faker->randomElement(['general', 'shop', 'shipping', 'payment', 'email']),
            'description' => $this->faker->sentence(),
        ];
    }
}
