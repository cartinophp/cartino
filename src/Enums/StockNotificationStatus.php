<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum StockNotificationStatus: string
{
    use TranslatableEnum;

    case PENDING = 'pending';
    case SENDING = 'sending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::SENDING => 'blue',
            self::SENT => 'green',
            self::FAILED => 'red',
            self::CANCELLED => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
