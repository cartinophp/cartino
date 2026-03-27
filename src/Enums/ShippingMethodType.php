<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum ShippingMethodType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case FLAT_RATE = 'flat_rate';
    case FREE = 'free';
    case CALCULATED = 'calculated';
    case PICKUP = 'pickup';

    public function requiresShipping(): bool
    {
        return $this !== self::PICKUP;
    }

    public function hasFixedCost(): bool
    {
        return in_array($this, [self::FLAT_RATE, self::FREE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
