<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default' => env('PAGINATION_DEFAULT', 15),
        'max' => env('PAGINATION_MAX', 100),
        'options' => [10, 15, 25, 50, 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('FILTERS_CACHE_ENABLED', true),
        'ttl' => env('FILTERS_CACHE_TTL', 3600), // 1 hour
        'tags' => ['filters', 'products'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Operators Configuration
    |--------------------------------------------------------------------------
    */
    'operators' => [
        'enabled' => [
            'eq', 'ne', 'gt', 'gte', 'lt', 'lte',
            'like', 'nlike', 'starts', 'ends',
            'in', 'nin', 'between', 'nbetween',
            'null', 'nnull', 'date', 'month', 'year',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Specific Settings
    |--------------------------------------------------------------------------
    */
    'models' => [
        'products' => [
            'per_page' => 20,
            'max_per_page' => 100,
            'default_sort' => '-created_at',
            'searchable' => ['name', 'description', 'sku'],
            'filterable' => ['price', 'category_id', 'brand_id', 'status'],
            'sortable' => ['name', 'price', 'created_at', 'stock'],
        ],

        'orders' => [
            'per_page' => 25,
            'max_per_page' => 50,
            'default_sort' => '-created_at',
            'searchable' => ['order_number', 'customer_email'],
            'filterable' => ['status', 'total', 'user_id'],
            'sortable' => ['created_at', 'total', 'status'],
        ],

        'customers' => [
            'per_page' => 30,
            'max_per_page' => 100,
            'default_sort' => '-created_at',
            'searchable' => ['name', 'email', 'phone'],
            'filterable' => ['status', 'country', 'created_at'],
            'sortable' => ['name', 'created_at', 'total_spent'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => env('FILTERS_RATE_LIMIT', true),
        'max_requests' => 60,
        'per_minutes' => 1,
    ],
];
