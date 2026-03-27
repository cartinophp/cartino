<?php

declare(strict_types=1);

namespace Cartino\Events\Entries;

use Cartino\Events\Event;

class EntryCreated extends Event
{
    public function __construct($entry)
    {
        parent::__construct([
            'entry' => $entry,
            'id' => $entry->id(),
            'slug' => $entry->slug(),
            'collection' => $entry->collectionHandle(),
            'type' => 'entry_created',
            'timestamp' => time(),
        ]);
    }

    public function entry()
    {
        return $this->get('entry');
    }

    public function id()
    {
        return $this->get('id');
    }

    public function slug()
    {
        return $this->get('slug');
    }

    public function collection()
    {
        return $this->get('collection');
    }

    public static function dispatch($entry)
    {
        return (new static($entry))->fire();
    }
}
