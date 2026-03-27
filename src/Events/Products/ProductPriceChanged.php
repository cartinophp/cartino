<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductPriceChanged extends Event
{
    public function __construct($product, $previousPrice, $newPrice)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'previous_price' => $previousPrice,
            'new_price' => $newPrice,
            'type' => 'product_price_changed',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function previousPrice()
    {
        return $this->get('previous_price');
    }

    public function newPrice()
    {
        return $this->get('new_price');
    }
}
