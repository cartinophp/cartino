<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductAssetAdded extends Event
{
    public function __construct($product, $asset)
    {
        parent::__construct([
            'product' => $product,
            'asset' => $asset,
            'entry' => $product,
            'type' => 'product_asset_added',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function asset()
    {
        return $this->get('asset');
    }
}
