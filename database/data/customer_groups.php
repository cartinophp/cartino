<?php

return [
    [
        'name' => 'Default Customers',
        'description' => 'Default customer group with standard pricing',
        'is_default' => true,
        'discount_percentage' => 0,
        'settings' => [
            'show_prices_with_tax' => true,
            'allow_backorders' => false,
            'minimum_order_amount' => 0,
        ],
    ],
    [
        'name' => 'VIP Customers',
        'description' => 'Premium customers with special pricing',
        'is_default' => false,
        'discount_percentage' => 10,
        'settings' => [
            'show_prices_with_tax' => true,
            'allow_backorders' => true,
            'minimum_order_amount' => 0,
        ],
    ],
    [
        'name' => 'Wholesale',
        'description' => 'Wholesale customers with bulk pricing',
        'is_default' => false,
        'discount_percentage' => 25,
        'settings' => [
            'show_prices_with_tax' => false,
            'allow_backorders' => true,
            'minimum_order_amount' => 500,
        ],
    ],
];
