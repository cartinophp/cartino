<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum ReturnStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case REFUNDED = 'refunded';

    public function color(): string
    {
        return match ($this) {
            self::REQUESTED => 'gray',
            self::APPROVED => 'primary',
            self::REJECTED => 'danger',
            self::IN_TRANSIT => 'info',
            self::RECEIVED => 'warning',
            self::PROCESSED => 'success',
            self::REFUNDED => 'success',
        };
    }

    public function canBeApproved(): bool
    {
        return $this === self::REQUESTED;
    }

    public function canBeRejected(): bool
    {
        return $this === self::REQUESTED;
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::REFUNDED, self::REJECTED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
