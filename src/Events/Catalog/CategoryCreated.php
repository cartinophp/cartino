<?php

declare(strict_types=1);

namespace Cartino\Events\Catalog;

use Cartino\Events\Event;

class CategoryCreated extends Event
{
    public function __construct($category)
    {
        parent::__construct([
            'category' => $category,
            'entry' => $category,
            'name' => $category->get('name'),
            'slug' => $category->get('slug'),
            'type' => 'category_created',
            'timestamp' => time(),
        ]);
    }

    public function category()
    {
        return $this->get('category');
    }

    public function name()
    {
        return $this->get('name');
    }
}
