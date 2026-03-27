<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class OrderStatuses extends BasicDictionary
{
    protected array $keywords = ['order', 'status', 'state'];

    protected function getItems(): array
    {
        return [
            [
                'value' => 'pending',
                'label' => __('cartino::dictionaries.order_pending'),
                'color' => 'gray',
                'icon' => 'clock',
            ],
            [
                'value' => 'confirmed',
                'label' => __('cartino::dictionaries.order_confirmed'),
                'color' => 'blue',
                'icon' => 'check',
            ],
            [
                'value' => 'processing',
                'label' => __('cartino::dictionaries.order_processing'),
                'color' => 'yellow',
                'icon' => 'cog',
            ],
            [
                'value' => 'shipped',
                'label' => __('cartino::dictionaries.order_shipped'),
                'color' => 'indigo',
                'icon' => 'truck',
            ],
            [
                'value' => 'delivered',
                'label' => __('cartino::dictionaries.order_delivered'),
                'color' => 'green',
                'icon' => 'check-circle',
            ],
            [
                'value' => 'cancelled',
                'label' => __('cartino::dictionaries.order_cancelled'),
                'color' => 'red',
                'icon' => 'x-circle',
            ],
            [
                'value' => 'refunded',
                'label' => __('cartino::dictionaries.order_refunded'),
                'color' => 'purple',
                'icon' => 'arrow-left',
            ],
        ];
    }
}
