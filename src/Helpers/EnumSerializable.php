<?php

declare(strict_types=1);

namespace Cartino\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @phpstan-ignore trait.unused */
trait EnumSerializable
{
    /**
     * Get an array with only the names of the enum.
     *
     * @return string[]
     */
    public static function toNamesArray(): array
    {
        return array_map(fn (self $case) => $case->name, self::cases());
    }

    /**
     * Get an array with only the values of the enum.
     *
     * @return mixed[]
     */
    public static function toValuesArray(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Get a collection of names and values of the enum.
     *
     * @phpstan-return array<int, array{name: string, value: mixed}>
     */
    public static function toArray(): array
    {
        return array_map(fn (self $case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }

    /**
     * Get the database value of a given enum value.
     */
    public function toValue(): Stringable
    {
        return Str::of($this->value)->replace('-', '_')->upper();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
