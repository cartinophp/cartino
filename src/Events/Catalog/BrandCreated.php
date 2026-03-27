<?php

declare(strict_types=1);

namespace Cartino\Events\Catalog;

use Cartino\Events\Event;

class BrandCreated extends Event
{
    public function __construct($brand)
    {
        parent::__construct([
            'brand' => $brand,
            'entry' => $brand,
            'name' => $brand->get('name'),
            'slug' => $brand->get('slug'),
            'type' => 'brand_created',
            'timestamp' => time(),
        ]);
    }

    public function brand()
    {
        return $this->get('brand');
    }

    public function name()
    {
        return $this->get('name');
    }
}
