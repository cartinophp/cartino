<?php

return [
    'title' => 'Categories',
    'single' => 'Category',
    'create' => 'New Category',
    'edit' => 'Edit Category',
    'list' => 'Category List',
    'search_placeholder' => 'Search categories...',
    'no_categories' => 'No categories found',

    // Fields
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'parent' => 'Parent Category',
        'image' => 'Image',
        'icon' => 'Icon',
        'sort_order' => 'Sort Order',
        'is_visible' => 'Visible',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'meta_keywords' => 'Meta Keywords',
    ],

    // Messages
    'messages' => [
        'created' => 'Category created successfully',
        'updated' => 'Category updated successfully',
        'deleted' => 'Category deleted successfully',
        'bulk_deleted' => 'Categories deleted successfully',
        'cannot_delete_parent' => 'Cannot delete a category with subcategories',
        'reordered' => 'Categories reordered successfully',
    ],

    'tree' => [
        'root' => 'Root',
        'expand_all' => 'Expand All',
        'collapse_all' => 'Collapse All',
        'move_up' => 'Move Up',
        'move_down' => 'Move Down',
        'make_child' => 'Make Child',
        'make_sibling' => 'Make Sibling',
    ],
];
