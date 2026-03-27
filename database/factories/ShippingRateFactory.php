<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Channel;
use Cartino\Models\ShippingRate;
use Cartino\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShippingRateFactory extends Factory
{
    protected $model = ShippingRate::class;

    public function definition(): array
    {
        return [
            'shipping_zone_id' => fn () => ShippingZone::factory(),
            'channel_id' => Channel::query()->inRandomOrder()->value('id'),
            'name' => 'Standard Shipping',
            'code' => Str::upper($this->faker->unique()->bothify('SHIP-###')),
            'description' => $this->faker->sentence(8),
            'calculation_method' => 'flat_rate',
            'price' => $this->faker->randomFloat(2, 5, 20),
            'currency' => 'EUR',
            'min_price' => null,
            'max_price' => null,
            'min_weight' => null,
            'max_weight' => null,
            'weight_unit' => 'kg',
            'min_order_value' => null,
            'max_order_value' => null,
            'min_delivery_days' => 2,
            'max_delivery_days' => 5,
            'carrier' => null,
            'service_code' => null,
            'carrier_settings' => null,
            'is_active' => true,
            'priority' => 0,
            'data' => null,
        ];
    }
}
