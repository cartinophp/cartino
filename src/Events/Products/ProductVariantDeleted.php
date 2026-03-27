<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductVariantDeleted extends Event
{
    public function __construct($product, $variant)
    {
        parent::__construct([
            'product' => $product,
            'variant' => $variant,
            'entry' => $product,
            'sku' => $variant->get('sku'),
            'type' => 'product_variant_deleted',
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
}
