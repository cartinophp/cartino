<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Cartino\Sites\Sites;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cartino\Models\Site|null get(string $handle)
 * @method static \Cartino\Models\Site|null find(int $id)
 * @method static \Cartino\Models\Site default()
 * @method static \Cartino\Models\Site current()
 * @method static \Illuminate\Support\Collection all()
 * @method static \Cartino\Models\Site selected()
 * @method static void setCurrent(string $handle)
 * @method static bool hasMultiple()
 *
 * @see Sites
 */
class Site extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cartino.sites';
    }
}
