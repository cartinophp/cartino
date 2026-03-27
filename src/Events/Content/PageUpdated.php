<?php

declare(strict_types=1);

namespace Cartino\Events\Content;

use Cartino\Events\Event;

class PageUpdated extends Event
{
    public function __construct($page, $original = null)
    {
        parent::__construct([
            'page' => $page,
            'original' => $original,
            'entry' => $page,
            'title' => $page->get('title'),
            'slug' => $page->get('slug'),
            'type' => 'page_updated',
            'timestamp' => time(),
        ]);
    }

    public function page()
    {
        return $this->get('page');
    }

    public function original()
    {
        return $this->get('original');
    }
}
