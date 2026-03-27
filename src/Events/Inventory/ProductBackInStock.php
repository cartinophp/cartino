<?php

declare(strict_types=1);

namespace Cartino\Events\Inventory;

use Cartino\Events\Event;

class ProductBackInStock extends Event
{
    public function __construct($product, int $quantity)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'quantity' => $quantity,
            'type' => 'product_back_in_stock',
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

    public function quantity()
    {
        return $this->get('quantity');
    }
}
