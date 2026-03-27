<?php

return [
    'menu' => [
        'title' => [
            'required' => 'The menu title is required.',
            'string' => 'The menu title must be a string.',
            'max' => 'The menu title cannot exceed :max characters.',
        ],
        'handle' => [
            'string' => 'The menu handle must be a string.',
            'max' => 'The menu handle cannot exceed :max characters.',
            'unique' => 'A menu with this handle already exists.',
        ],
        'description' => [
            'string' => 'The description must be a string.',
        ],
        'settings' => [
            'array' => 'The settings must be an array.',
        ],
        'is_active' => [
            'boolean' => 'The active status must be true or false.',
        ],
    ],

    'address' => [
        'customer_id' => [
            'required' => 'The customer field is required.',
            'exists' => 'The selected customer does not exist.',
        ],
        'type' => [
            'required' => 'The address type is required.',
            'enum' => 'The address type must be billing or shipping.',
        ],
        'first_name' => [
            'required' => 'The first name is required.',
            'string' => 'The first name must be a string.',
            'max' => 'The first name cannot exceed :max characters.',
        ],
        'last_name' => [
            'required' => 'The last name is required.',
            'string' => 'The last name must be a string.',
            'max' => 'The last name cannot exceed :max characters.',
        ],
        'company' => [
            'string' => 'The company must be a string.',
            'max' => 'The company cannot exceed :max characters.',
        ],
        'address_line_1' => [
            'required' => 'The address line 1 is required.',
            'string' => 'The address line 1 must be a string.',
            'max' => 'The address line 1 cannot exceed :max characters.',
        ],
        'address_line_2' => [
            'string' => 'The address line 2 must be a string.',
            'max' => 'The address line 2 cannot exceed :max characters.',
        ],
        'city' => [
            'required' => 'The city is required.',
            'string' => 'The city must be a string.',
            'max' => 'The city cannot exceed :max characters.',
        ],
        'state' => [
            'string' => 'The state must be a string.',
            'max' => 'The state cannot exceed :max characters.',
        ],
        'postal_code' => [
            'required' => 'The postal code is required.',
            'string' => 'The postal code must be a string.',
            'max' => 'The postal code cannot exceed :max characters.',
        ],
        'country_code' => [
            'required' => 'The country code is required.',
            'string' => 'The country code must be a string.',
            'size' => 'The country code must be exactly :size characters.',
        ],
        'phone' => [
            'string' => 'The phone must be a string.',
            'max' => 'The phone cannot exceed :max characters.',
        ],
        'is_default' => [
            'boolean' => 'The default status must be true or false.',
        ],
    ],

    'wishlist' => [
        'customer_id' => [
            'required' => 'The customer field is required.',
            'exists' => 'The selected customer does not exist.',
        ],
        'name' => [
            'required' => 'The wishlist name is required.',
            'string' => 'The wishlist name must be a string.',
            'max' => 'The wishlist name cannot exceed :max characters.',
        ],
        'description' => [
            'string' => 'The description must be a string.',
            'max' => 'The description cannot exceed :max characters.',
        ],
        'status' => [
            'enum' => 'The status must be a valid wishlist status.',
        ],
        'is_shared' => [
            'boolean' => 'The shared status must be true or false.',
        ],
    ],

    'cart' => [
        'session_id' => [
            'string' => 'The session ID must be a string.',
            'max' => 'The session ID cannot exceed :max characters.',
        ],
        'customer_id' => [
            'exists' => 'The selected customer does not exist.',
        ],
        'email' => [
            'email' => 'The email must be a valid email address.',
            'max' => 'The email cannot exceed :max characters.',
        ],
        'status' => [
            'enum' => 'The status must be a valid cart status.',
        ],
        'items' => [
            'array' => 'The items must be an array.',
            'product_id' => [
                'required' => 'The product ID is required for each item.',
                'exists' => 'The selected product does not exist.',
            ],
            'quantity' => [
                'required' => 'The quantity is required for each item.',
                'integer' => 'The quantity must be an integer.',
                'min' => 'The quantity must be at least :min.',
                'max' => 'The quantity cannot exceed :max.',
            ],
            'price' => [
                'required' => 'The price is required for each item.',
                'numeric' => 'The price must be a number.',
                'min' => 'The price must be at least :min.',
            ],
        ],
        'subtotal' => [
            'numeric' => 'The subtotal must be a number.',
            'min' => 'The subtotal must be at least :min.',
        ],
        'tax_amount' => [
            'numeric' => 'The tax amount must be a number.',
            'min' => 'The tax amount must be at least :min.',
        ],
        'shipping_amount' => [
            'numeric' => 'The shipping amount must be a number.',
            'min' => 'The shipping amount must be at least :min.',
        ],
        'discount_amount' => [
            'numeric' => 'The discount amount must be a number.',
            'min' => 'The discount amount must be at least :min.',
        ],
        'total_amount' => [
            'numeric' => 'The total amount must be a number.',
            'min' => 'The total amount must be at least :min.',
        ],
        'currency' => [
            'string' => 'The currency must be a string.',
            'size' => 'The currency must be exactly :size characters.',
        ],
        'shipping_address' => [
            'array' => 'The shipping address must be an array.',
        ],
        'billing_address' => [
            'array' => 'The billing address must be an array.',
        ],
        'metadata' => [
            'array' => 'The metadata must be an array.',
        ],
    ],

    'stock_notification' => [
        'user_id' => [
            'required' => 'The user field is required.',
            'exists' => 'The selected user does not exist.',
        ],
        'product_id' => [
            'required' => 'The product field is required.',
            'exists' => 'The selected product does not exist.',
        ],
        'email' => [
            'required' => 'The email is required.',
            'email' => 'The email must be a valid email address.',
            'max' => 'The email cannot exceed :max characters.',
        ],
        'status' => [
            'enum' => 'The status must be a valid notification status.',
        ],
    ],
];
