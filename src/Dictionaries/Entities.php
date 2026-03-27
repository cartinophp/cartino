<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class Entities extends BasicDictionary
{
    protected string $valueKey = 'model';

    protected array $keywords = ['entities', 'entity', 'model', 'data'];

    protected function getItemLabel(array $item): string
    {
        return "{$item['label']} ({$item['model']})";
    }

    protected $entities = [
        [
            'model' => 'User',
            'label' => 'User',
        ],
        [
            'model' => 'Brand',
            'label' => 'Brand',
        ],
        [
            'model' => 'ProductType',
            'label' => 'Product Type',
        ],
        [
            'model' => 'Collection',
            'label' => 'Collection',
        ],
        [
            'model' => 'Tag',
            'label' => 'Tag',
        ],
        [
            'model' => 'Vendor',
            'label' => 'Vendor',
        ],
        [
            'model' => 'Product',
            'label' => 'Product',
        ],
        [
            'model' => 'ProductVariant',
            'label' => 'Product Variant',
        ],
        [
            'model' => 'Customer',
            'label' => 'Customer',
        ],
        [
            'model' => 'Order',
            'label' => 'Order',
        ],
        [
            'model' => 'PriceList',
            'label' => 'Price List',
        ],
    ];

    protected function getItems(): array
    {
        return $this->entities;
    }
}
