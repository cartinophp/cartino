<?php

return [
    'title' => 'Products',
    'single' => 'Product',
    'create' => 'New Product',
    'edit' => 'Edit Product',
    'list' => 'Product List',
    'search_placeholder' => 'Search products...',
    'no_products' => 'No products found',

    // Fields
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'excerpt' => 'Excerpt',
        'sku' => 'SKU Code',
        'price' => 'Price',
        'compare_price' => 'Compare Price',
        'cost_price' => 'Cost Price',
        'weight' => 'Weight',
        'weight_unit' => 'Weight Unit',
        'category' => 'Category',
        'brand' => 'Brand',
        'vendor' => 'Vendor',
        'barcode' => 'Barcode',
        'track_quantity' => 'Track Quantity',
        'quantity' => 'Quantity',
        'min_quantity' => 'Minimum Quantity',
        'security_stock' => 'Security Stock',
        'stock_status' => 'Stock Status',
        'backorder' => 'Backorder',
        'require_shipping' => 'Requires Shipping',
        'is_digital' => 'Digital Product',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'meta_keywords' => 'Meta Keywords',
        'featured' => 'Featured',
        'is_visible' => 'Visible',
        'publish_date' => 'Publish Date',
        'images' => 'Images',
        'gallery' => 'Gallery',
        'variants' => 'Variants',
        'attributes' => 'Attributes',
        'tags' => 'Tags',
        'related_products' => 'Related Products',
        'cross_selling' => 'Cross-selling',
        'up_selling' => 'Up-selling',
    ],

    // Tabs
    'tabs' => [
        'general' => 'General',
        'pricing' => 'Pricing',
        'inventory' => 'Inventory',
        'shipping' => 'Shipping',
        'seo' => 'SEO',
        'media' => 'Media',
        'variants' => 'Variants',
        'attributes' => 'Attributes',
        'related' => 'Related Products',
        'reviews' => 'Reviews',
    ],

    // Stock status
    'stock_status' => [
        'in_stock' => 'In Stock',
        'out_of_stock' => 'Out of Stock',
        'on_backorder' => 'On Backorder',
        'low_stock' => 'Low Stock',
    ],

    // Weight units
    'weight_units' => [
        'kg' => 'Kilograms',
        'g' => 'Grams',
        'lb' => 'Pounds',
        'oz' => 'Ounces',
    ],

    // Product types
    'types' => [
        'simple' => 'Simple',
        'variable' => 'Variable',
        'grouped' => 'Grouped',
        'external' => 'External',
        'digital' => 'Digital',
    ],

    // Messages
    'messages' => [
        'created' => 'Product created successfully',
        'updated' => 'Product updated successfully',
        'deleted' => 'Product deleted successfully',
        'bulk_deleted' => 'Products deleted successfully',
        'published' => 'Product published successfully',
        'unpublished' => 'Product hidden successfully',
        'duplicated' => 'Product duplicated successfully',
        'restored' => 'Product restored successfully',
        'featured' => 'Product featured',
        'unfeatured' => 'Product unfeatured',
        'stock_updated' => 'Stock updated successfully',
        'price_updated' => 'Price updated successfully',
        'sku_exists' => 'This SKU code already exists',
        'slug_exists' => 'This slug already exists',
        'bulk_activated' => ':count products activated successfully',
        'bulk_archived' => ':count products archived successfully',
        'bulk_exported' => 'Exporting :count products',
    ],

    // Bulk actions
    'bulk_actions' => [
        'delete' => 'Delete Selected',
        'publish' => 'Publish Selected',
        'unpublish' => 'Hide Selected',
        'feature' => 'Feature',
        'unfeature' => 'Unfeature',
        'duplicate' => 'Duplicate Selected',
        'export' => 'Export Selected',
        'update_category' => 'Update Category',
        'update_brand' => 'Update Brand',
        'update_price' => 'Update Price',
        'update_stock' => 'Update Stock',
    ],

    // Filters
    'filters' => [
        'all_products' => 'All Products',
        'published' => 'Published',
        'draft' => 'Draft',
        'archived' => 'Archived',
        'featured' => 'Featured',
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Low Stock',
        'price_range' => 'Price Range',
        'category' => 'By Category',
        'brand' => 'By Brand',
        'vendor' => 'By Vendor',
        'created_date' => 'Created Date',
        'updated_date' => 'Updated Date',
        'has_images' => 'With Images',
        'no_images' => 'Without Images',
        'has_variants' => 'With Variants',
        'no_variants' => 'Without Variants',
        'digital' => 'Digital',
        'physical' => 'Physical',
    ],

    // Import/Export
    'import' => [
        'title' => 'Import Products',
        'description' => 'Upload a CSV or Excel file to import products',
        'download_template' => 'Download Template',
        'mapping' => 'Field Mapping',
        'preview' => 'Import Preview',
        'errors' => 'Import Errors',
        'success' => ':count products imported successfully',
    ],

    'export' => [
        'title' => 'Export Products',
        'description' => 'Export products in CSV or Excel format',
        'fields_to_export' => 'Fields to Export',
        'file_format' => 'File Format',
        'export_all' => 'Export All',
        'export_selected' => 'Export Selected',
        'export_filtered' => 'Export Filtered',
    ],

    // Variants
    'variants' => [
        'title' => 'Product Variants',
        'create' => 'Create Variant',
        'edit' => 'Edit Variant',
        'none' => 'No variants',
        'options' => 'Variant Options',
        'combinations' => 'Combinations',
        'generate' => 'Generate Variants',
        'bulk_edit' => 'Bulk Edit',
        'price_difference' => 'Price Difference',
        'weight_difference' => 'Weight Difference',
        'sku_pattern' => 'SKU Pattern',
        'auto_generate_sku' => 'Auto Generate SKU',
    ],

    // Reviews
    'reviews' => [
        'title' => 'Reviews',
        'count' => 'Review Count',
        'average_rating' => 'Average Rating',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'stars' => 'stars',
    ],
];
