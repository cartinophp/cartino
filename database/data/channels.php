<?php

return [
    [
        'name' => 'Default Store',
        'slug' => 'default',
        'description' => 'Main e-commerce store',
        'url' => null,
        'is_default' => true,

        'settings' => [
            'theme' => 'default',
            'logo' => null,
            'favicon' => null,
            'analytics' => [
                'google_analytics_id' => null,
                'facebook_pixel_id' => null,
            ],
            'seo' => [
                'meta_title' => 'Cartino Store',
                'meta_description' => 'Modern e-commerce store powered by Cartino',
                'meta_keywords' => 'ecommerce, laravel, vue, shopping',
            ],
            'social' => [
                'facebook' => null,
                'twitter' => null,
                'instagram' => null,
                'youtube' => null,
            ],
        ],
    ],
];
