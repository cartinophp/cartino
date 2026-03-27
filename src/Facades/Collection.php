<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Cartino\Collections\CategoryRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cartino\Collections\Category|null find(string $handle)
 * @method static \Cartino\Collections\Category|null findByHandle(string $handle)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Cartino\Collections\Category make(string $handle)
 * @method static void save(\Cartino\Collections\Category $collection)
 * @method static void delete(\Cartino\Collections\Category $collection)
 *
 * @see CategoryRepository
 */
class Category extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cartino.collections';
    }
}
