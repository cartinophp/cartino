<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        $handle = Str::slug($name);

        return [
            'handle' => $handle,
            'name' => $name,
            'description' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'domain' => $this->faker->domainName(),
            'locale' => $this->faker->locale(),
            'lang' => $this->faker->languageCode(),
            'countries' => $this->faker->randomElements(['IT', 'FR', 'DE', 'ES', 'US'], 2),
            'default_currency' => $this->faker->currencyCode(),
            'tax_included_in_prices' => $this->faker->boolean(),
            'tax_region' => $this->faker->countryCode(),
            'priority' => $this->faker->numberBetween(1, 10),
            'is_default' => false,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'order' => $this->faker->numberBetween(1, 100),
            'published_at' => $this->faker->boolean(80) ? now() : null,
            'unpublished_at' => null,
            'attributes' => [],
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
