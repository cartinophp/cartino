<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class PaymentStatuses extends BasicDictionary
{
    protected array $keywords = ['payment', 'status', 'transaction'];

    protected function getItems(): array
    {
        return [
            ['value' => 'pending', 'label' => __('cartino::dictionaries.payment_pending'), 'color' => 'gray'],
            ['value' => 'authorized', 'label' => __('cartino::dictionaries.payment_authorized'), 'color' => 'blue'],
            ['value' => 'captured', 'label' => __('cartino::dictionaries.payment_captured'), 'color' => 'green'],
            ['value' => 'failed', 'label' => __('cartino::dictionaries.payment_failed'), 'color' => 'red'],
            ['value' => 'refunded', 'label' => __('cartino::dictionaries.payment_refunded'), 'color' => 'purple'],
            [
                'value' => 'partially_refunded',
                'label' => __('cartino::dictionaries.payment_partially_refunded'),
                'color' => 'orange',
            ],
        ];
    }
}
