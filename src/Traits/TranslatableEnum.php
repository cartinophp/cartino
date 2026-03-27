<?php

declare(strict_types=1);

namespace Cartino\Traits;

trait TranslatableEnum
{
    /**
     * Get the translated label for this enum case.
     * Falls back to a humanized version of the case name if no translation exists.
     */
    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $enumClass = class_basename(static::class);
        $key = 'cartino::enums.'.$enumClass.'.'.$this->value;

        $translated = __($key, locale: $locale);

        // If translation key not found, return humanized value
        if ($translated === $key) {
            return ucfirst(str_replace(['_', '-'], ' ', $this->value));
        }

        return $translated;
    }

    /**
     * Get all cases as a translated [value => label] array.
     */
    public static function translatedOptions(?string $locale = null): array
    {
        return collect(static::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label($locale)])
            ->all();
    }
}
