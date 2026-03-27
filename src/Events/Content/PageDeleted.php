<?php

declare(strict_types=1);

namespace Cartino\Events\Content;

use Cartino\Events\Event;

class PageDeleted extends Event
{
    public function __construct($page)
    {
        parent::__construct([
            'page' => $page,
            'entry' => $page,
            'title' => $page->get('title'),
            'slug' => $page->get('slug'),
            'type' => 'page_deleted',
            'timestamp' => time(),
        ]);
    }

    public function page()
    {
        return $this->get('page');
    }

    public function title()
    {
        return $this->get('title');
    }
}
