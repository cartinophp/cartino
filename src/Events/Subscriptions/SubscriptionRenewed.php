<?php

declare(strict_types=1);

namespace Cartino\Events\Subscriptions;

use Cartino\Events\Event;

class SubscriptionRenewed extends Event
{
    public function __construct($subscription, $order = null)
    {
        parent::__construct([
            'subscription' => $subscription,
            'order' => $order,
            'entry' => $subscription,
            'type' => 'subscription_renewed',
            'timestamp' => time(),
        ]);
    }

    public function subscription()
    {
        return $this->get('subscription');
    }

    public function order()
    {
        return $this->get('order');
    }
}
