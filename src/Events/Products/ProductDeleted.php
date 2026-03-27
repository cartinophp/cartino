<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductDeleted extends Event
{
    public function __construct($product)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'title' => $product->get('title'),
            'type' => 'product_deleted',
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

    public function title()
    {
        return $this->get('title');
    }
}
