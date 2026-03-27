<?php

return [
    'discount' => 'Discount',
    'discounts' => 'Discounts',
    'create_discount' => 'Create Discount',
    'edit_discount' => 'Edit Discount',
    'discount_details' => 'Discount Details',
    'manage_discounts' => 'Manage Discounts',

    'fields' => [
        'name' => 'Name',
        'description' => 'Description',
        'code' => 'Code',
        'type' => 'Type',
        'value' => 'Value',
        'minimum_order_amount' => 'Minimum Order Amount',
        'maximum_discount_amount' => 'Maximum Discount Amount',
        'usage_limit' => 'Usage Limit',
        'usage_limit_per_customer' => 'Usage Limit Per Customer',
        'is_enabled' => 'Enabled',
        'starts_at' => 'Starts At',
        'expires_at' => 'Expires At',
        'eligible_customers' => 'Eligible Customers',
        'eligible_products' => 'Eligible Products',
        'usage_count' => 'Usage Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'types' => [
        'percentage' => 'Percentage',
        'fixed_amount' => 'Fixed Amount',
        'free_shipping' => 'Free Shipping',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'expired' => 'Expired',
        'upcoming' => 'Upcoming',
    ],

    'messages' => [
        'created_successfully' => 'Discount created successfully.',
        'updated_successfully' => 'Discount updated successfully.',
        'deleted_successfully' => 'Discount deleted successfully.',
        'enabled_successfully' => 'Discount enabled successfully.',
        'disabled_successfully' => 'Discount disabled successfully.',
        'duplicated_successfully' => 'Discount duplicated successfully.',
        'delete_failed' => 'Unable to delete discount.',
        'code_not_found' => 'Discount code not found.',
        'code_inactive' => 'Discount code is not active.',
        'code_valid' => 'Discount code is valid.',
        'usage_limit_exceeded' => 'Usage limit exceeded.',
        'minimum_order_not_met' => 'Minimum order amount not met.',
        'not_eligible' => 'Not eligible for this discount.',
        'applied_successfully' => 'Discount applied successfully.',
        'removed_successfully' => 'Discount removed successfully.',
    ],

    'validation' => [
        'name_required' => 'Name is required.',
        'code_unique' => 'This code is already in use.',
        'code_format' => 'The code may only contain letters and numbers.',
        'type_required' => 'Discount type is required.',
        'type_invalid' => 'Invalid discount type.',
        'value_required' => 'Value is required.',
        'value_numeric' => 'Value must be a number.',
        'percentage_max' => 'Percentage cannot exceed 100%.',
        'expires_after_starts' => 'Expiry date must be after start date.',
        'customer_not_found' => 'Customer not found.',
        'product_not_found' => 'Product not found.',
    ],

    'placeholders' => [
        'search' => 'Search by name or code...',
        'name' => 'e.g. Summer Sale 2024',
        'description' => 'Optional discount description',
        'code' => 'e.g. SUMMER2024 (leave blank to auto-generate)',
        'value' => 'e.g. 10 for 10% or 5.00 for $5',
        'minimum_order_amount' => 'e.g. 50.00',
        'maximum_discount_amount' => 'e.g. 20.00',
        'usage_limit' => 'e.g. 100 (leave blank for unlimited)',
        'usage_limit_per_customer' => 'e.g. 1 (leave blank for unlimited)',
    ],

    'actions' => [
        'create' => 'Create Discount',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'duplicate' => 'Duplicate',
        'view' => 'View',
        'apply' => 'Apply',
        'remove' => 'Remove',
        'validate_code' => 'Validate Code',
    ],

    'statistics' => [
        'total_discounts' => 'Total Discounts',
        'active_discounts' => 'Active Discounts',
        'total_applications' => 'Total Applications',
        'total_discount_amount' => 'Total Discount Amount',
        'usage_percentage' => 'Usage Percentage',
        'unique_customers' => 'Unique Customers',
        'applications_count' => 'Applications',
        'discount_amount' => 'Discount Amount',
    ],

    'filters' => [
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'expired' => 'Expired',
        'by_type' => 'By Type',
        'by_status' => 'By Status',
    ],

    'confirmations' => [
        'delete' => 'Are you sure you want to delete this discount?',
        'disable' => 'Are you sure you want to disable this discount?',
        'enable' => 'Are you sure you want to enable this discount?',
    ],

    'empty_states' => [
        'no_discounts' => 'No discounts found.',
        'no_applications' => 'This discount has not been used yet.',
        'no_eligible_customers' => 'No eligible customers specified.',
        'no_eligible_products' => 'No eligible products specified.',
    ],
];
