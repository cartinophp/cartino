<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Cartino\Blueprints\BlueprintRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cartino\Blueprints\Blueprint|null find(string $handle)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Cartino\Blueprints\Blueprint make(string $handle)
 * @method static void save(\Cartino\Blueprints\Blueprint $blueprint)
 *
 * @see BlueprintRepository
 */
class Blueprint extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cartino.blueprints';
    }
}
