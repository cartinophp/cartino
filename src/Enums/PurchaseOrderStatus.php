<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum PurchaseOrderStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case DRAFT = 'draft';
    case SENT = 'sent';
    case CONFIRMED = 'confirmed';
    case PARTIAL = 'partial';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'info',
            self::CONFIRMED => 'primary',
            self::PARTIAL => 'warning',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function canBeEdited(): bool
    {
        return $this === self::DRAFT;
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }

    public function canReceiveItems(): bool
    {
        return in_array($this, [self::CONFIRMED, self::PARTIAL]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
