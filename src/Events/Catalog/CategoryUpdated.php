<?php

declare(strict_types=1);

namespace Cartino\Events\Catalog;

use Cartino\Events\Event;

class CategoryUpdated extends Event
{
    public function __construct($category, $original = null)
    {
        parent::__construct([
            'category' => $category,
            'original' => $original,
            'entry' => $category,
            'name' => $category->get('name'),
            'type' => 'category_updated',
            'timestamp' => time(),
        ]);
    }

    public function category()
    {
        return $this->get('category');
    }

    public function original()
    {
        return $this->get('original');
    }
}
