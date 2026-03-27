<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum FulfillmentStatus: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case UNFULFILLED = 'unfulfilled';
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function isComplete(): bool
    {
        return in_array($this, [self::DELIVERED, self::FAILED, self::CANCELLED]);
    }
}
