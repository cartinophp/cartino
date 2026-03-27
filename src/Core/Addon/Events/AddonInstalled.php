<?php

declare(strict_types=1);

namespace Cartino\Core\Addon\Events;

use Cartino\Core\Addon\AddonInterface;

class PluginInstalled
{
    public function __construct(
        public AddonInterface $addon,
    ) {}
}
