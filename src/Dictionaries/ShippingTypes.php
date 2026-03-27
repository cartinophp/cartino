<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class ShippingTypes extends BasicDictionary
{
    protected array $keywords = ['shipping', 'delivery', 'fulfillment'];

    protected function getItems(): array
    {
        return [
            [
                'value' => 'flat_rate',
                'label' => __('cartino::dictionaries.shipping_flat_rate'),
                'description' => 'Fixed cost shipping',
            ],
            ['value' => 'free', 'label' => __('cartino::dictionaries.shipping_free'), 'description' => 'Free shipping'],
            [
                'value' => 'calculated',
                'label' => __('cartino::dictionaries.shipping_calculated'),
                'description' => 'Calculated based on weight/distance',
            ],
            [
                'value' => 'pickup',
                'label' => __('cartino::dictionaries.shipping_pickup'),
                'description' => 'Local pickup - no shipping',
            ],
            [
                'value' => 'express',
                'label' => __('cartino::dictionaries.shipping_express'),
                'description' => 'Express/overnight delivery',
            ],
        ];
    }
}
