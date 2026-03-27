<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum WeightUnit: string
{
    use TranslatableEnum;

    case Kilogram = 'kg';
    case Gram = 'g';
    case Pound = 'lb';
    case Ounce = 'oz';

    public function abbreviation(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum InventoryPolicy: string
{
    use TranslatableEnum;

    case Deny = 'deny';
    case Continue = 'continue';

    public function allowsOutOfStockSales(): bool
    {
        return $this === self::Continue;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum InventoryManagement: string
{
    use TranslatableEnum;

    case Shopify = 'shopify';
    case NotManaged = 'not_managed';
    case FulfillmentService = 'fulfillment_service';

    public function tracksInventory(): bool
    {
        return $this !== self::NotManaged;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum CollectionType: string
{
    use TranslatableEnum;

    case Manual = 'manual';
    case Smart = 'smart';
    case Custom = 'custom';

    public function description(): string
    {
        return match ($this) {
            self::Manual => 'Add products manually',
            self::Smart => 'Products added automatically based on conditions',
            self::Custom => 'Custom collection logic',
        };
    }

    public function isAutomated(): bool
    {
        return $this === self::Smart;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum PublishedScope: string
{
    use TranslatableEnum;

    case Web = 'web';
    case Global = 'global';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
