<?php

declare(strict_types=1);

namespace Cartino\Events\Inventory;

use Cartino\Events\Event;

class ProductLowStock extends Event
{
    public function __construct($product, int $quantity, int $threshold)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'quantity' => $quantity,
            'threshold' => $threshold,
            'type' => 'product_low_stock',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function quantity()
    {
        return $this->get('quantity');
    }

    public function threshold()
    {
        return $this->get('threshold');
    }
}
