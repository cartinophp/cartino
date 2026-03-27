<?php

declare(strict_types=1);

namespace Cartino\Support;

use Illuminate\Support\Arr as BaseArr;

class Arr extends BaseArr
{
    /**
     * Remove null values from array
     *
     * @return array
     */
    public static function removeNullValues(array $array)
    {
        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Remove empty values from array
     *
     * @return array
     */
    public static function removeEmpty(array $array)
    {
        return array_filter($array, function ($value) {
            return ! empty($value) || $value === 0 || $value === false;
        });
    }
}
