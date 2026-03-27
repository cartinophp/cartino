<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum AppInstallationStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'warning',
            self::CANCELLED => 'danger',
        };
    }

    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeReactivated(): bool
    {
        return in_array($this, [self::INACTIVE, self::SUSPENDED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
