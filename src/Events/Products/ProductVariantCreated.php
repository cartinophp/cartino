<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductVariantCreated extends Event
{
    public function __construct($product, $variant)
    {
        parent::__construct([
            'product' => $product,
            'variant' => $variant,
            'entry' => $product,
            'sku' => $variant->get('sku'),
            'type' => 'product_variant_created',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function variant()
    {
        return $this->get('variant');
    }

    public function sku()
    {
        return $this->get('sku');
    }
}
