<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductVariantUpdated extends Event
{
    public function __construct($product, $variant, $original = null)
    {
        parent::__construct([
            'product' => $product,
            'variant' => $variant,
            'original' => $original,
            'entry' => $product,
            'sku' => $variant->get('sku'),
            'type' => 'product_variant_updated',
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

    public function original()
    {
        return $this->get('original');
    }
}
