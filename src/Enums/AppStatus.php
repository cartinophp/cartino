<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum AppStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DEPRECATED = 'deprecated';

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::DEPRECATED => 'orange',
        };
    }

    public function isPublished(): bool
    {
        return $this === self::APPROVED;
    }

    public function canBeInstalled(): bool
    {
        return in_array($this, [self::APPROVED, self::DEPRECATED]);
    }

    public function canBeEdited(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
