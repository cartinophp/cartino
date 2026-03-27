<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum StockReservationStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case RESERVED = 'reserved';
    case FULFILLED = 'fulfilled';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function color(): string
    {
        return match ($this) {
            self::RESERVED => 'warning',
            self::FULFILLED => 'success',
            self::CANCELLED => 'gray',
            self::EXPIRED => 'danger',
        };
    }

    public function isActive(): bool
    {
        return $this === self::RESERVED;
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::FULFILLED, self::CANCELLED, self::EXPIRED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
