<?php

declare(strict_types=1);

namespace Cartino\Events\Apps;

use Cartino\Events\Event;

class AppStatusChanged extends Event
{
    public function __construct($app, string $previousStatus, string $newStatus)
    {
        parent::__construct([
            'app' => $app,
            'entry' => $app,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'app_name' => $app->get('name'),
            'type' => 'app_status_changed',
            'timestamp' => time(),
        ]);
    }

    public function app()
    {
        return $this->get('app');
    }

    public function previousStatus()
    {
        return $this->get('previous_status');
    }

    public function newStatus()
    {
        return $this->get('new_status');
    }
}
