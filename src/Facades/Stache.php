<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cartino\Stache\Store store(string $name)
 * @method static \Cartino\Stache\Stores\Store|null getStore(string $name)
 * @method static void registerStore(string $name, string $class)
 * @method static void clear()
 * @method static void warm()
 * @method static string generateId()
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void put(string $key, mixed $value)
 *
 * @see \Cartino\Stache\Stache
 */
class Stache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stache';
    }
}
