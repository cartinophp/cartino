<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum TransactionType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case CAPTURE = 'capture';
    case VOID = 'void';

    public function color(): string
    {
        return match ($this) {
            self::PAYMENT => 'success',
            self::REFUND => 'warning',
            self::CAPTURE => 'info',
            self::VOID => 'gray',
        };
    }

    public function affectsBalance(): bool
    {
        return in_array($this, [self::PAYMENT, self::CAPTURE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
