<?php

declare(strict_types=1);

namespace Cartino\Events\Collections;

use Cartino\Events\Event;

class CollectionDeleting extends Event
{
    public function __construct($collection)
    {
        parent::__construct([
            'collection' => $collection,
            'entry' => $collection,
            'handle' => $collection->handle(),
            'type' => 'collection_deleting',
            'timestamp' => time(),
        ]);
    }

    public function collection()
    {
        return $this->get('collection');
    }

    public function handle()
    {
        return $this->get('handle');
    }

    public static function dispatch($collection)
    {
        return (new static($collection))->fire();
    }
}
