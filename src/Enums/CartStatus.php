<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum CartStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';
    case EXPIRED = 'expired';

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::ABANDONED => 'orange',
            self::CONVERTED => 'blue',
            self::EXPIRED => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
