<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\ShippingZone;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'name' => 'EU Zone '.$this->faker->numberBetween(1, 99),
            'description' => $this->faker->sentence(8),
            'countries' => ['IT', 'FR', 'DE', 'ES'],
            'regions' => null,
            'postal_codes' => null,
            'priority' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'data' => null,
        ];
    }
}
