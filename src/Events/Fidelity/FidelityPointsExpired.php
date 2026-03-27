<?php

declare(strict_types=1);

namespace Cartino\Events\Fidelity;

use Cartino\Events\Event;

class FidelityPointsExpired extends Event
{
    public function __construct($customer, int $points)
    {
        parent::__construct([
            'customer' => $customer,
            'entry' => $customer,
            'points' => $points,
            'type' => 'fidelity_points_expired',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function points()
    {
        return $this->get('points');
    }
}
