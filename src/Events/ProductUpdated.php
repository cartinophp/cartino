<?php

declare(strict_types=1);

namespace Cartino\Events;

class ProductUpdated extends Event
{
    public function __construct($product, $original = null)
    {
        parent::__construct([
            'product' => $product,
            'original' => $original,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'price' => $product->get('price'),
            'type' => 'product_updated',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function original()
    {
        return $this->get('original');
    }

    public function priceChanged()
    {
        return $this->hasChanged('price');
    }

    public function skuChanged()
    {
        return $this->hasChanged('sku');
    }

    public function inventoryChanged()
    {
        return $this->hasChanged('inventory');
    }

    protected function hasChanged($field)
    {
        if (! ($original = $this->original())) {
            return true;
        }

        return $this->product()->get($field) !== $original->get($field);
    }
}
