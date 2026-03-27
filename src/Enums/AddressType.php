<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum AddressType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case BILLING = 'billing';
    case SHIPPING = 'shipping';
    case BOTH = 'both';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
