<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;

class ProductCollection extends BaseResourceCollection
{
    /**
     * Transform individual product item.
     */
    protected function transformItem($product, Request $request): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'handle' => $product->handle,
            'status' => $product->status,
            'visibility' => $product->visibility,
            'price' => [
                'amount' => $product->price / 100,
                'formatted' => number_format($product->price / 100, 2),
                'currency' => 'USD',
            ],
            'inventory' => $product->inventory_quantity,
            'sku' => $product->sku,
            'image' => $product->featured_image
                ? [
                    'url' => $product->featured_image,
                    'thumb' => $product->featured_image_thumb,
                ] : null,
            'category' => $product->category
                ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
            'brand' => $product->brand
                ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                ] : null,
            'variants_count' => $product->variants_count ?? 0,
            'created_at' => $product->created_at?->toISOString(),
            'updated_at' => $product->updated_at?->toISOString(),
        ];
    }

    /**
     * Get collection-specific meta.
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'available_filters' => $this->getAvailableFilters(),
            'available_sorts' => $this->getAvailableSorts(),
            'bulk_actions' => $this->getBulkActions(),
        ]);
    }

    /**
     * Get available filters for products.
     */
    protected function getAvailableFilters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ],
            ],
            [
                'key' => 'visibility',
                'label' => 'Visibility',
                'type' => 'select',
                'options' => [
                    ['value' => 'public', 'label' => 'Public'],
                    ['value' => 'hidden', 'label' => 'Hidden'],
                    ['value' => 'private', 'label' => 'Private'],
                ],
            ],
            [
                'key' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'options_url' => '/api/categories/options',
            ],
            [
                'key' => 'brand_id',
                'label' => 'Brand',
                'type' => 'select',
                'options_url' => '/api/brands/options',
            ],
            [
                'key' => 'price_range',
                'label' => 'Price Range',
                'type' => 'range',
                'min' => 0,
                'max' => 10000,
            ],
            [
                'key' => 'inventory_status',
                'label' => 'Inventory',
                'type' => 'select',
                'options' => [
                    ['value' => 'in_stock', 'label' => 'In Stock'],
                    ['value' => 'low_stock', 'label' => 'Low Stock'],
                    ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
                ],
            ],
        ];
    }

    /**
     * Get available sort options.
     */
    protected function getAvailableSorts(): array
    {
        return [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'price', 'label' => 'Price'],
            ['field' => 'inventory_quantity', 'label' => 'Inventory'],
            ['field' => 'created_at', 'label' => 'Created Date'],
            ['field' => 'updated_at', 'label' => 'Updated Date'],
        ];
    }

    /**
     * Get available bulk actions.
     */
    protected function getBulkActions(): array
    {
        return [
            [
                'key' => 'activate',
                'label' => 'Activate',
                'icon' => 'check-circle',
                'destructive' => false,
            ],
            [
                'key' => 'archive',
                'label' => 'Archive',
                'icon' => 'archive',
                'destructive' => false,
            ],
            [
                'key' => 'delete',
                'label' => 'Delete',
                'icon' => 'trash',
                'destructive' => true,
                'confirmation' => 'Are you sure you want to delete these products?',
            ],
            [
                'key' => 'export',
                'label' => 'Export',
                'icon' => 'download',
                'destructive' => false,
            ],
        ];
    }
}
