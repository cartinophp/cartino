<?php

declare(strict_types=1);

namespace Cartino\Events\Assets;

use Cartino\Events\Event;

class AssetUploaded extends Event
{
    public function __construct($asset, $container = null)
    {
        parent::__construct([
            'asset' => $asset,
            'container' => $container,
            'entry' => $asset,
            'path' => $asset->get('path'),
            'mime_type' => $asset->get('mime_type'),
            'type' => 'asset_uploaded',
            'timestamp' => time(),
        ]);
    }

    public function asset()
    {
        return $this->get('asset');
    }

    public function container()
    {
        return $this->get('container');
    }

    public function path()
    {
        return $this->get('path');
    }

    public function mimeType()
    {
        return $this->get('mime_type');
    }
}
