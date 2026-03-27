<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum ProductType: string
{
    use TranslatableEnum;

    case Physical = 'physical';
    case Digital = 'digital';
    case Service = 'service';
    case Subscription = 'subscription';
    case Bundle = 'bundle';

    public function requiresShipping(): bool
    {
        return $this === self::Physical;
    }

    public function hasInventory(): bool
    {
        return in_array($this, [self::Physical, self::Bundle]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
