<?php

declare(strict_types=1);

namespace Cartino\Events\Fidelity;

use Cartino\Events\Event;

class FidelityPointsEarned extends Event
{
    public function __construct($customer, int $points, ?string $reason = null)
    {
        parent::__construct([
            'customer' => $customer,
            'entry' => $customer,
            'points' => $points,
            'reason' => $reason,
            'type' => 'fidelity_points_earned',
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

    public function reason()
    {
        return $this->get('reason');
    }
}
