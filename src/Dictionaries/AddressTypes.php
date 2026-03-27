<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class AddressTypes extends BasicDictionary
{
    protected array $keywords = ['address', 'shipping', 'billing'];

    protected function getItems(): array
    {
        return [
            [
                'value' => 'billing',
                'label' => __('cartino::dictionaries.address_type_billing'),
                'icon' => 'credit-card',
            ],
            ['value' => 'shipping', 'label' => __('cartino::dictionaries.address_type_shipping'), 'icon' => 'truck'],
            ['value' => 'both', 'label' => __('cartino::dictionaries.address_type_both'), 'icon' => 'both'],
        ];
    }
}
