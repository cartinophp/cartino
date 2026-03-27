<?php

declare(strict_types=1);

namespace Cartino\Events;

class ProductCreated extends Event
{
    public function __construct($product)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'price' => $product->get('price'),
            'type' => 'product_created',
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

    public function price()
    {
        return $this->get('price');
    }
}
