<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum DiscountType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case FREE_SHIPPING = 'free_shipping';
    case BUY_X_GET_Y = 'buy_x_get_y';

    public function requiresValue(): bool
    {
        return in_array($this, [self::PERCENTAGE, self::FIXED_AMOUNT]);
    }

    public function requiresQuantityRules(): bool
    {
        return $this === self::BUY_X_GET_Y;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
