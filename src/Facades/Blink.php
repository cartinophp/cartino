<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void put(string $key, mixed $value)
 * @method static bool has(string $key)
 * @method static void forget(string $key)
 * @method static void flush()
 * @method static array all()
 * @method static mixed store(string $store)
 *
 * @see \Cartino\Support\Blink
 */
class Blink extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cartino.blink';
    }
}
