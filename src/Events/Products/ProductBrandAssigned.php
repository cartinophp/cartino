<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductBrandAssigned extends Event
{
    public function __construct($product, $brand)
    {
        parent::__construct([
            'product' => $product,
            'brand' => $brand,
            'entry' => $product,
            'type' => 'product_brand_assigned',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function brand()
    {
        return $this->get('brand');
    }
}
