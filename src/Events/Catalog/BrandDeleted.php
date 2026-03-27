<?php

declare(strict_types=1);

namespace Cartino\Events\Catalog;

use Cartino\Events\Event;

class BrandDeleted extends Event
{
    public function __construct($brand)
    {
        parent::__construct([
            'brand' => $brand,
            'entry' => $brand,
            'name' => $brand->get('name'),
            'type' => 'brand_deleted',
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
