<?php

declare(strict_types=1);

namespace Cartino\Events\Assets;

use Cartino\Events\Event;

class AssetDeleted extends Event
{
    public function __construct($asset)
    {
        parent::__construct([
            'asset' => $asset,
            'entry' => $asset,
            'path' => $asset->get('path'),
            'type' => 'asset_deleted',
            'timestamp' => time(),
        ]);
    }

    public function asset()
    {
        return $this->get('asset');
    }

    public function path()
    {
        return $this->get('path');
    }
}
