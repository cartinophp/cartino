<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum StockStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case OnBackorder = 'on_backorder';
    case LowStock = 'low_stock';
    case Discontinued = 'discontinued';

    public function color(): string
    {
        return match ($this) {
            self::InStock => 'green',
            self::OutOfStock => 'red',
            self::OnBackorder => 'yellow',
            self::LowStock => 'orange',
            self::Discontinued => 'gray',
        };
    }

    public function isAvailable(): bool
    {
        return in_array($this, [self::InStock, self::OnBackorder, self::LowStock]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
