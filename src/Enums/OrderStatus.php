<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum OrderStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::CONFIRMED => 'blue',
            self::PROCESSING => 'purple',
            self::SHIPPED => 'indigo',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
