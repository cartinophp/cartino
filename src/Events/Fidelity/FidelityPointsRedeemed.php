<?php

declare(strict_types=1);

namespace Cartino\Events\Fidelity;

use Cartino\Events\Event;

class FidelityPointsRedeemed extends Event
{
    public function __construct($customer, int $points, $order = null)
    {
        parent::__construct([
            'customer' => $customer,
            'order' => $order,
            'entry' => $customer,
            'points' => $points,
            'type' => 'fidelity_points_redeemed',
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

    public function order()
    {
        return $this->get('order');
    }
}
