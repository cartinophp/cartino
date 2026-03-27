<?php

declare(strict_types=1);

namespace Cartino\Events\Assets;

use Cartino\Events\Event;

class AssetTransformed extends Event
{
    public function __construct($asset, string $transformation)
    {
        parent::__construct([
            'asset' => $asset,
            'entry' => $asset,
            'transformation' => $transformation,
            'path' => $asset->get('path'),
            'type' => 'asset_transformed',
            'timestamp' => time(),
        ]);
    }

    public function asset()
    {
        return $this->get('asset');
    }

    public function transformation()
    {
        return $this->get('transformation');
    }

    public function path()
    {
        return $this->get('path');
    }
}
