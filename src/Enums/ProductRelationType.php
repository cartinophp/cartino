<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum ProductRelationType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case UPSELL = 'upsell';
    case CROSS_SELL = 'cross_sell';
    case RELATED = 'related';
    case ALTERNATIVE = 'alternative';
    case ACCESSORY = 'accessory';
}
