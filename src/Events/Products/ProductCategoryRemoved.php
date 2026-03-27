<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductCategoryRemoved extends Event
{
    public function __construct($product, $category)
    {
        parent::__construct([
            'product' => $product,
            'category' => $category,
            'entry' => $product,
            'type' => 'product_category_removed',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function category()
    {
        return $this->get('category');
    }
}
