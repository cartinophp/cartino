<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum DiscountTargetType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case ALL = 'all';
    case SPECIFIC_PRODUCTS = 'specific_products';
    case SPECIFIC_COLLECTIONS = 'specific_collections';
    case CATEGORIES = 'categories';

    public function requiresSelection(): bool
    {
        return $this !== self::ALL;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
