<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum TransactionStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }

    public function canBeRefunded(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canBeCancelled(): bool
    {
        return $this === self::PENDING;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
