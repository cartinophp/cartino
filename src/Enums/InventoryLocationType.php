<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum InventoryLocationType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case WAREHOUSE = 'warehouse';
    case STORE = 'store';
    case DROPSHIP = 'dropship';
    case VENDOR = 'vendor';
}
