<?php

declare(strict_types=1);

namespace Cartino\Events\Catalog;

use Cartino\Events\Event;

class BrandUpdated extends Event
{
    public function __construct($brand, $original = null)
    {
        parent::__construct([
            'brand' => $brand,
            'original' => $original,
            'entry' => $brand,
            'name' => $brand->get('name'),
            'type' => 'brand_updated',
            'timestamp' => time(),
        ]);
    }

    public function brand()
    {
        return $this->get('brand');
    }

    public function original()
    {
        return $this->get('original');
    }
}
