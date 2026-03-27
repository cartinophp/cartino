<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum StockMovementType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN = 'return';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case DAMAGE = 'damage';

    public function color(): string
    {
        return match ($this) {
            self::PURCHASE, self::RETURN => 'success',
            self::SALE, self::TRANSFER => 'primary',
            self::ADJUSTMENT => 'info',
            self::DAMAGE => 'danger',
        };
    }

    public function affectsQuantity(): int
    {
        return match ($this) {
            self::PURCHASE, self::RETURN => 1, // aumenta
            self::SALE, self::DAMAGE => -1, // diminuisce
            self::TRANSFER, self::ADJUSTMENT => 0, // varia
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this, [self::PURCHASE, self::SALE, self::RETURN]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
