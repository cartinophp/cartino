<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Market;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MarketFactory extends Factory
{
    protected $model = Market::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        $handle = Str::slug($name);
        $type = $this->faker->randomElement(['b2c', 'b2b', 'wholesale', 'marketplace']);
        $countryCode = $this->faker->countryCode();

        return [
            'handle' => $handle,
            'name' => ucwords($name),
            'code' => strtoupper($countryCode.'-'.$type),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'countries' => $this->faker->randomElements(['IT', 'FR', 'DE', 'ES', 'GB', 'US'], 2),
            'default_currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'supported_currencies' => $this->faker->randomElements(['EUR', 'USD', 'GBP', 'CHF'], 2),
            'default_locale' => $this->faker->locale(),
            'supported_locales' => $this->faker->randomElements(['en_US', 'it_IT', 'fr_FR', 'de_DE'], 2),
            'tax_included_in_prices' => $this->faker->boolean(),
            'tax_region' => $countryCode,
            'catalog_id' => null,
            'use_catalog_prices' => false,
            'payment_methods' => $this->faker->randomElements(['stripe', 'paypal', 'bank_transfer', 'invoice'], 2),
            'shipping_methods' => $this->faker->randomElements(['standard', 'express', 'same_day'], 2),
            'fulfillment_locations' => null,
            'priority' => $this->faker->numberBetween(1, 10),
            'is_default' => false,
            'status' => 'active',
            'order' => $this->faker->numberBetween(1, 100),
            'published_at' => $this->faker->boolean(80) ? now() : null,
            'unpublished_at' => null,
            'settings' => [],
            'metadata' => [],
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

    public function b2c(): self
    {
        return $this->state(['type' => 'b2c']);
    }

    public function b2b(): self
    {
        return $this->state(['type' => 'b2b']);
    }

    public function wholesale(): self
    {
        return $this->state(['type' => 'wholesale']);
    }

    public function forCountry(string $countryCode): self
    {
        return $this->state([
            'countries' => [$countryCode],
            'code' => strtoupper($countryCode.'-'.$this->faker->randomElement(['b2c', 'b2b'])),
        ]);
    }

    public function withCatalog(): self
    {
        return $this->state([
            'catalog_id' => 1,
            'use_catalog_prices' => true,
        ]);
    }
}
