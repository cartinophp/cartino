<?php

declare(strict_types=1);

namespace Cartino\Events\Apps;

use Cartino\Events\Event;

class AppInstalled extends Event
{
    public function __construct($app, $installation)
    {
        parent::__construct([
            'app' => $app,
            'installation' => $installation,
            'entry' => $app,
            'app_name' => $app->get('name'),
            'type' => 'app_installed',
            'timestamp' => time(),
        ]);
    }

    public function app()
    {
        return $this->get('app');
    }

    public function installation()
    {
        return $this->get('installation');
    }

    public function appName()
    {
        return $this->get('app_name');
    }
}
