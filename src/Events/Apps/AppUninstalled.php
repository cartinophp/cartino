<?php

declare(strict_types=1);

namespace Cartino\Events\Apps;

use Cartino\Events\Event;

class AppUninstalled extends Event
{
    public function __construct($app)
    {
        parent::__construct([
            'app' => $app,
            'entry' => $app,
            'app_name' => $app->get('name'),
            'type' => 'app_uninstalled',
            'timestamp' => time(),
        ]);
    }

    public function app()
    {
        return $this->get('app');
    }

    public function appName()
    {
        return $this->get('app_name');
    }
}
