<?php

declare(strict_types=1);

namespace Cartino\Events\Inventory;

use Cartino\Events\Event;

class ProductOutOfStock extends Event
{
    public function __construct($product)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'title' => $product->get('title'),
            'type' => 'product_out_of_stock',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function sku()
    {
        return $this->get('sku');
    }
}
