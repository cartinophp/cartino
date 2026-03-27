<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum ReturnReason: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case DEFECTIVE = 'defective';
    case WRONG_ITEM = 'wrong_item';
    case NOT_AS_DESCRIBED = 'not_as_described';
    case CHANGED_MIND = 'changed_mind';
    case DAMAGED = 'damaged';
    case OTHER = 'other';

    public function requiresInspection(): bool
    {
        return in_array($this, [self::DEFECTIVE, self::DAMAGED, self::NOT_AS_DESCRIBED]);
    }

    public function isCustomerFault(): bool
    {
        return in_array($this, [self::CHANGED_MIND, self::OTHER]);
    }

    public function mayIncurRestockingFee(): bool
    {
        return $this->isCustomerFault();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
