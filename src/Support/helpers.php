<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

if (! function_exists('cp_route')) {
    /**
     * Generate a URL to a Control Panel route.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function cp_route($name, $parameters = [], $absolute = true)
    {
        $cpPrefix = config('cartino.cp.route_prefix', 'cp');

        // Prepend CP prefix to route name if not already present
        if (! str_starts_with($name, $cpPrefix.'.')) {
            $name = $cpPrefix.'.'.$name;
        }

        return route($name, $parameters, $absolute);
    }
}

if (! function_exists('cp_url')) {
    /**
     * Generate a URL to a Control Panel path.
     *
     * @param  string  $path
     * @return string
     */
    function cp_url($path = '')
    {
        $cpPrefix = config('cartino.cp.route_prefix', 'cp');

        return url($cpPrefix.'/'.ltrim($path, '/'));
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     * Alias for __() function for Cartino namespace.
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    function trans($key, $replace = [], $locale = null)
    {
        return __($key, $replace, $locale);
    }
}
