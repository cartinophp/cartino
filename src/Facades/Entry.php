<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Cartino\Contracts\Entries\EntryRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cartino\Entries\Entry|null find(string $id)
 * @method static \Cartino\Entries\Entry|null findBySlug(string $slug, string $collection)
 * @method static \Illuminate\Support\Collection whereCollection(string $collection)
 * @method static \Cartino\Entries\Entry make()
 * @method static void save(\Cartino\Entries\Entry $entry)
 * @method static void delete(\Cartino\Entries\Entry $entry)
 * @method static \Illuminate\Database\Eloquent\Builder query()
 *
 * @see EntryRepository
 */
class Entry extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cartino.entries';
    }
}
