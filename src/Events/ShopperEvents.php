<?php

namespace Cartino\Events;

class EntryCreated extends Event
{
    public function __construct($entry)
    {
        parent::__construct([
            'entry' => $entry,
            'collection' => $entry->collection(),
            'type' => 'entry_created',
            'timestamp' => time(),
        ]);
    }

    public function entry()
    {
        return $this->get('entry');
    }

    public function collection()
    {
        return $this->get('collection');
    }
}

class EntryUpdated extends Event
{
    public function __construct($entry, $original = null)
    {
        parent::__construct([
            'entry' => $entry,
            'original' => $original,
            'collection' => $entry->collection(),
            'type' => 'entry_updated',
            'timestamp' => time(),
        ]);
    }

    public function entry()
    {
        return $this->get('entry');
    }

    public function original()
    {
        return $this->get('original');
    }

    public function collection()
    {
        return $this->get('collection');
    }

    public function hasChanged($field = null)
    {
        if (! ($original = $this->original())) {
            return true;
        }

        if ($field) {
            return $this->entry()->get($field) !== $original->get($field);
        }

        return $this->entry()->data() !== $original->data();
    }
}

class EntryDeleted extends Event
{
    public function __construct($entry)
    {
        parent::__construct([
            'entry' => $entry,
            'collection' => $entry->collection(),
            'type' => 'entry_deleted',
            'timestamp' => time(),
        ]);
    }

    public function entry()
    {
        return $this->get('entry');
    }

    public function collection()
    {
        return $this->get('collection');
    }
}

class CollectionCreated extends Event
{
    public function __construct($collection)
    {
        parent::__construct([
            'collection' => $collection,
            'handle' => $collection->handle(),
            'type' => 'collection_created',
            'timestamp' => time(),
        ]);
    }

    public function collection()
    {
        return $this->get('collection');
    }
}

class CollectionUpdated extends Event
{
    public function __construct($collection, $original = null)
    {
        parent::__construct([
            'collection' => $collection,
            'original' => $original,
            'handle' => $collection->handle(),
            'type' => 'collection_updated',
            'timestamp' => time(),
        ]);
    }

    public function collection()
    {
        return $this->get('collection');
    }

    public function original()
    {
        return $this->get('original');
    }
}

class CollectionDeleted extends Event
{
    public function __construct($collection)
    {
        parent::__construct([
            'collection' => $collection,
            'handle' => $collection->handle(),
            'type' => 'collection_deleted',
            'timestamp' => time(),
        ]);
    }

    public function collection()
    {
        return $this->get('collection');
    }
}
