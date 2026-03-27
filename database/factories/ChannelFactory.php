<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Channel;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        $slug = Str::slug($name);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id') ?? Site::factory(),
            'name' => ucfirst($name),
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['web', 'mobile', 'api', 'pos']),
            'url' => $this->faker->url(),
            'is_default' => false,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'locales' => $this->faker->randomElements(
                ['en', 'it', 'fr', 'de', 'es'],
                $this->faker->numberBetween(1, 3),
            ),
            'currencies' => $this->faker->randomElements(['EUR', 'USD', 'GBP'], $this->faker->numberBetween(1, 2)),
            'settings' => [
                'theme' => $this->faker->word(),
                'logo' => $this->faker->imageUrl(),
            ],
        ];
    }

    public function default(): self
    {
        return $this->state(['is_default' => true, 'status' => 'active']);
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }
}
