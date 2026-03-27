<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum Gender: string
{
    use TranslatableEnum;

    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
    case PreferNotToSay = 'prefer_not_to_say';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
