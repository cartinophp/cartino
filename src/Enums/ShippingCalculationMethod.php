<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum ShippingCalculationMethod: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case FLAT_RATE = 'flat_rate';
    case PER_ITEM = 'per_item';
    case WEIGHT_BASED = 'weight_based';
    case PRICE_BASED = 'price_based';
    case CARRIER_CALCULATED = 'carrier_calculated';

    public function requiresWeight(): bool
    {
        return $this === self::WEIGHT_BASED;
    }

    public function requiresPrice(): bool
    {
        return $this === self::PRICE_BASED;
    }

    public function requiresExternalAPI(): bool
    {
        return $this === self::CARRIER_CALCULATED;
    }

    public function requiresItemCount(): bool
    {
        return $this === self::PER_ITEM;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
