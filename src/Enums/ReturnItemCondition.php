<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum ReturnItemCondition: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case NEW = 'new';
    case OPENED = 'opened';
    case DAMAGED = 'damaged';
    case DEFECTIVE = 'defective';

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'success',
            self::OPENED => 'warning',
            self::DAMAGED => 'danger',
            self::DEFECTIVE => 'danger',
        };
    }

    public function isResellable(): bool
    {
        return in_array($this, [self::NEW, self::OPENED]);
    }

    public function restockPercentage(): int
    {
        return match ($this) {
            self::NEW => 100,
            self::OPENED => 80,
            self::DAMAGED => 0,
            self::DEFECTIVE => 0,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
