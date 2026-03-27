<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;
use Cartino\Traits\TranslatableEnum;

enum MenuItemType: string
{
    use EnumSerializable;
    use TranslatableEnum;

    case LINK = 'link';
    case PAGE = 'page';
    case CATEGORY = 'category';
    case PRODUCT = 'product';
    case CUSTOM = 'custom';
}
